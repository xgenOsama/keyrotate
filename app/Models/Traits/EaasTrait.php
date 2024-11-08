<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Crypt;

trait EaasTrait {
    // Encrypt attributes before saving to the database
    public function setAttribute($key, $value)
    {
        // Check if the row has a specific vault value before applying encryption
        if (in_array($key, $this->encryptedColumns) && $this->shouldEncrypt()) {
            // Encrypt the value if it's in the encrypted columns list
            $value = Crypt::encryptString($value);
        }
        return parent::setAttribute($key, $value);
    }

    // Decrypt attributes after retrieving from the database
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Check if the row has a specific vault value before applying decryption
        if (in_array($key, $this->encryptedColumns) && $this->shouldDecrypt()) {
            // Decrypt the value if it's in the encrypted columns list
            $value = Crypt::decryptString($value);
        }

        return $value;
    }

    // Method to check if the column should be encrypted
    private function shouldEncrypt()
    {
        // Check for specific condition, e.g., if the row has a certain vault value
        return $this->encryption_key_version === null || isset($this->encryption_key_version);  // Replace 'specific_vault_value' with your actual condition
    }

    // Method to check if the column should be decrypted
    private function shouldDecrypt()
    {
        // Check for specific condition before decryption
        return $this->encryption_key_version !== null;  // Replace 'specific_vault_value' with your actual condition
    }

    protected function getEncryptionKeyVersion(): string
    {
        // Example version; replace with actual key version retrieval logic
        return 'v1';  // e.g., retrieve from a config or vault system
    }

    protected function setEncryptionKeyVersion()
    {
        // Example version; replace with actual key version retrieval logic
        $this->encryption_key_version = $this->getEncryptionKeyVersion();
    }

    protected static function booted()
    {
        // Trigger encryption logic on saving (for both create and update)
        static::saving(function ($model) {
            // You can add update-specific logic here if needed
            $model->setEncryptionKeyVersion();
        });

        // Optionally, add logic specific to updates
        static::updating(function ($model) {
            // You can add update-specific logic here if needed
            $model->setEncryptionKeyVersion();
        });
    }
}