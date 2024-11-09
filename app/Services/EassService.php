<?php

namespace App\Services;

use App\Models\EncryptionKey;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class EassService
{
    private const METHOD = 'aes-256-cbc';
    private $key = "";
    public function __construct() {
        $this->key = $this->getEncryptionKey();
    }
    /**
     * Encrypt a value with a specific key.
     *
     * @param  string  $value
     * @param  string  $key
     * @return string
     */
    public function encrypt(string $value): string
    {
        // Generate an initialization vector (IV)
        $iv = random_bytes(openssl_cipher_iv_length(self::METHOD));
        // Encrypt the value using the key and IV
        $encrypted = openssl_encrypt($value, self::METHOD, $this->key, 0, $iv);
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }
        // Encode the IV and encrypted data together for storage
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a value with a specific key.
     *
     * @param  string  $encryptedValue
     * @param  string  $key
     * @return string
     * @throws Exception
     */
    public function decrypt(string $encryptedValue): string
    {
        // Decode the base64-encoded value
        $decoded = base64_decode($encryptedValue);
        // Get the IV length for the encryption method
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        // Extract the IV and encrypted data
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);
        // Decrypt the data using the key and IV
        $decrypted = openssl_decrypt($encryptedData, self::METHOD, $this->key, 0, $iv);
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }
        return $decrypted;
    }

    /**
     * Decrypt a value with a specific key.
     *
     * @param  string  $encryptedValue
     * @param  string  $key
     * @return string
     * @throws Exception
     */
    public function decryptByVersion(string $encryptedValue, $key_version): string
    {
        $key = $this->getKeyByVersion($key_version);
        if($key == ""){
            return "";
        }
        // Decode the base64-encoded value
        $decoded = base64_decode($encryptedValue);

        // Get the IV length for the encryption method
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        // Extract the IV and encrypted data
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);
        // Decrypt the data using the key and IV
        $decrypted = openssl_decrypt($encryptedData, self::METHOD, $key, 0, $iv);
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }
        return $decrypted;
    }

    private function getEncryptionKey() : string{
        /// get from cache key , key_version
        $encryptionKey = Cache::get('encryption_key', []);
        if (!empty($encryptionKey)) {
            return $encryptionKey["key"];
        }else{
            /// if not in cahce retrive from database or vault 
            $key = EncryptionKey::latest('created_at')->first();;
            if(isset($key)){
                $keyData = ['key' => $key->key, 'key_version' => "v".$key->version];
                Cache::put('encryption_key', $keyData, now()->addMinutes(2)); // Cache for 10 minutes
                $this->rotateKey();
                // return the key 
                return $key->key;
            }
        }
    }

    public function getEncryptionKeyVersion(): string{
        $encryptionKey = Cache::get('encryption_key', []);
        if (!empty($encryptionKey)) {
            return $encryptionKey["key_version"];
        }else{
            /// if not in cahce retrive from database or vault 
            $key = EncryptionKey::latest('created_at')->first();;
            if(isset($key)){
                $keyData = ['key' => $key->key, 'key_version' => "v".$key->version];
                Cache::put('encryption_key', $keyData, now()->addMinutes(2)); // Cache for 10 minutes
                $this->rotateKey();
                // return the key 
                return "v".$key->version;
            }
        }
    }

    private function getKeyByVersion($key_version): string{
        $encryptionKey = Cache::get('encryption_key_'.$key_version, []);
        if (!empty($encryptionKey)) {
            return $encryptionKey["key"];
        }else{
            $version = explode("v",$key_version)[1];
            $key = EncryptionKey::where('version',$version)->first();
            if(isset($key)){
                $keyData = ['key' => $key->key, 'key_version' => "v".$key->version];
                Cache::put('encryption_key_'.$key_version, $keyData, now()->addMinutes(360)); // Cache for 360 minutes
                return $key->key;
            }else{
                return "";
            }
        }
    }

    private function rotateKey()
    {
        // Generate a new encryption key
        $newKey = base64_encode(Str::random(32));
        $old_key = EncryptionKey::latest('created_at')->first();
        $key = new EncryptionKey();
        $key->version = $old_key->version +1;
        $key->key = $newKey;
        $key->save();
    }
}
