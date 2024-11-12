<?php

namespace App\Services;

class VaultService
{
    private $vaultAddress;
    private $token;

    public function __construct() {
        $this->vaultAddress = env('vaultAddress',"http://localhost:8200");
        $this->token = env('vaultToken',"root");
    }

    public function ensureSecretPathEnabled() {
        // Step 1: Verify if the secret path is enabled
        $url = "{$this->vaultAddress}/v1/sys/mounts";
        $headers = [
            "X-Vault-Token: {$this->token}"
        ];
        $response = $this->sendRequest($url, null, $headers, 'GET');
    
        // Check if the secret/ path is already enabled
        if (isset($response['secret/'])) {
            return ['success' => 'Secret path is already enabled'];
        }
    
        // Step 2: If not enabled, enable the secret path
        $url = "{$this->vaultAddress}/v1/sys/mounts/secret";
        $payload = json_encode([
            "type" => "kv",
            "options" => ["version" => "2"] // KV version 2 supports versioning
        ]);
        $headers = [
            "X-Vault-Token: {$this->token}",
            "Content-Type: application/json"
        ];
        $response = $this->sendRequest($url, $payload, $headers, 'POST');
    
        if (isset($response['error'])) {
            return ['error' => $response['error']];
        }
    
        return ['success' => 'Secret path has been enabled'];
    }

    // Function to store data in Vault
    public function storeData($data,$path = "secret/data/key") {
        $this->ensureSecretPathEnabled();
        $url = "{$this->vaultAddress}/v1/{$path}";
        $payload = json_encode(['data' => $data]);
        $headers = [
            "X-Vault-Token: {$this->token}",
            "Content-Type: application/json"
        ];
        $response = $this->sendRequest($url, $payload, $headers, 'POST');
        if (isset($response['errors'])) {
            return ['error' => $response['errors']];
        }
        return ['success' => true , "data" => $response["data"]];
    }

    // Function to retrieve data from Vault
    public function getData($path="secret/data/key") {
        $this->ensureSecretPathEnabled();
        $url = "{$this->vaultAddress}/v1/{$path}";
        $headers = [
            "X-Vault-Token: {$this->token}"
        ];
        $response = $this->sendRequest($url, null, $headers, 'GET');
        if (isset($response['errors'])) {
            return ['error' => $response['errors']];
        }
        return $response['data'] ?? null;
    }

    // Function to retrieve data from Vault by version
    public function getDataByVersion($version,$path = "secret/data/key") {
        $this->ensureSecretPathEnabled();
        $url = "{$this->vaultAddress}/v1/{$path}?version={$version}";
        $headers = [
            "X-Vault-Token: {$this->token}"
        ];
        $response = $this->sendRequest($url, null, $headers, 'GET');
        
        if (isset($response['errors'])) {
            return ['error' => $response['errors']];
        }
        return $response['data'] ?? null;
    }



    private function sendRequest($url, $payload = null, $headers = [], $method = 'GET', $maxRetries = 3, $retryDelay = 1) {
        $attempt = 0;
        while ($attempt < $maxRetries) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($method === 'POST' && $payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            if ($error) {
                $attempt++;
                curl_close($ch);
                // If max retries reached, return the last error
                if ($attempt >= $maxRetries) {
                    return ['error' => curl_strerror($error)];
                }
                // Wait before retrying
                sleep($retryDelay);
            } else {
                curl_close($ch);
                return json_decode($response, true);
            }
        }
        // If all retries fail, return a generic error
        return ['error' => 'Request failed after maximum retries'];
    }
}
