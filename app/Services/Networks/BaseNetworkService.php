<?php

namespace App\Services\Networks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseNetworkService implements NetworkServiceInterface
{
    protected string $networkName;
    protected array $requiredFields = [];
    protected array $defaultConfig = [];
    
    /**
     * Test connection to the network API
     */
    abstract public function testConnection(array $credentials): array;
    
    /**
     * Get required fields for this network
     */
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }
    
    /**
     * Get default configuration for this network
     */
    public function getDefaultConfig(): array
    {
        return $this->defaultConfig;
    }
    
    /**
     * Validate credentials
     */
    public function validateCredentials(array $credentials): array
    {
        $errors = [];
        
        foreach ($this->requiredFields as $field) {
            if (empty($credentials[$field])) {
                $errors[$field] = "The {$field} field is required.";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Make HTTP request with error handling
     */
    protected function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            $http = Http::timeout(30);
            
            // Add headers if provided
            if (isset($options['headers'])) {
                $http = $http->withHeaders($options['headers']);
                unset($options['headers']);
            }
            
            // Handle query parameters
            if (isset($options['query'])) {
                $url .= '?' . http_build_query($options['query']);
                unset($options['query']);
            }
            
            // Make request based on method
            if (strtolower($method) === 'get') {
                $response = $http->get($url);
            } elseif (strtolower($method) === 'post') {
                // Handle form_params or json
                if (isset($options['form_params'])) {
                    $response = $http->asForm()->post($url, $options['form_params']);
                } else {
                    $response = $http->post($url, $options);
                }
            } else {
                $response = $http->$method($url, $options);
            }
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'body' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error("Network API Request Error ({$this->networkName}): " . $e->getMessage());
            
            return [
                'success' => false,
                'status' => 0,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}

