<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class EaasService
{
    private const METHOD = 'aes-256-cbc';
    private $key = "";
    private $vaultClient;
    public function __construct() {
        $this->vaultClient = new VaultService();
        $this->key = $this->getEncryptionKey();
    }
    /**
     * Encrypt a value with a specific key.
     *
     * @param  string  $value
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


    public function encryptFile(string $filePath): string
    {
        // Read file contents
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new Exception('Unable to read file.');
        }

        // Generate an initialization vector (IV)
        $iv = random_bytes(openssl_cipher_iv_length(self::METHOD));

        // Encrypt the file contents using the key and IV
        $encrypted = openssl_encrypt($fileContents, self::METHOD, $this->key, 0, $iv);
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        // Combine IV and encrypted data, then encode them for storage
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a value with a specific key.
     *
     * @param  string  $encryptedValue
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
            return $encryptedValue;
            //throw new Exception('Decryption failed');
        }
        return $decrypted;
    }


    public function decryptFile(string $encryptedFilePath,$key_version)
    {
        $key = $this->getKeyByVersion($key_version);
        // Read the encrypted file content
        $encryptedFileContent = file_get_contents($encryptedFilePath);
        if ($encryptedFileContent === false) {
            throw new Exception('Failed to read encrypted file.');
        }
        // Decode the base64-encoded content
        $decoded = base64_decode($encryptedFileContent);
        if ($decoded === false) {
            throw new Exception('Invalid encrypted file content.');
        }
        // Get the IV length for the encryption method
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        // Extract the IV and encrypted data
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);
        // Decrypt the data using the key and IV
        $decryptedFile = openssl_decrypt($encryptedData, self::METHOD, $key, 0, $iv);
        if ($decryptedFile === false) {
            throw new Exception('Decryption failed');
        }
        return $decryptedFile;
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
            return $encryptedValue;
            //throw new Exception('Decryption failed');
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
            $this->rotateKey();
            $key_data = $this->vaultClient->getData();
            if(isset($key_data["data"])){
                $keyData = ['key' => $key_data["data"]["key"], 'key_version' => "v".$key_data["metadata"]["version"]];
                Cache::put('encryption_key', $keyData, now()->addMinutes(1)); // Cache for 10 minutes                // return the key 
                return $key_data["data"]["key"];
            }else{
                return "";
            }
        }
    }

    public function getEncryptionKeyVersion(): string{
        $encryptionKey = Cache::get('encryption_key', []);
        if (!empty($encryptionKey)) {
            return $encryptionKey["key_version"];
        }else{
            /// if not in cahce retrive from database or vault 
            $this->rotateKey();
            $key_data = $this->vaultClient->getData();
            if(isset($key_data["data"])){
                $keyData = ['key' => $key_data["data"]["key"], 'key_version' => "v".$key_data["metadata"]["version"]];
                Cache::put('encryption_key', $keyData, now()->addMinutes(1)); // Cache for 10 minutes
                // return the key 
                return "v".$key_data["metadata"]["version"];
            }else {
                return "";
            }
        }
    }

    private function getKeyByVersion($key_version): string{
        $encryptionKey = Cache::get('encryption_key_'.$key_version, []);
        if (!empty($encryptionKey)) {
            return $encryptionKey["key"];
        }else{
            $version = explode("v",$key_version)[1];
            $key_data = $this->vaultClient->getDataByVersion($version);
            if(isset($key_data["data"])){
                $keyData = ['key' => $key_data["data"]["key"], 'key_version' => "v".$key_data["metadata"]["version"]];
                Cache::put('encryption_key_'.$key_version, $keyData, now()->addMinutes(360)); // Cache for 360 minutes
                return $key_data["data"]["key"];
            }else{
                return "";
            }
        }
    }

    private function rotateKey()
    {
        // Generate a new encryption key
        $newKey = base64_encode(Str::random(32));
        $this->vaultClient->storeData([
            "key" => $newKey
        ]);
    }
}
