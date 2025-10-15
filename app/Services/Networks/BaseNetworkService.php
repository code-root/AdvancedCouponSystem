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
        
        // Check if requiredFields is an associative array (new format) or simple array (old format)
        foreach ($this->requiredFields as $fieldKey => $fieldValue) {
            // If it's associative array with field config
            if (is_array($fieldValue)) {
                $fieldName = $fieldKey;
                $isRequired = $fieldValue['required'] ?? true;
                $label = $fieldValue['label'] ?? $fieldName;
                
                if ($isRequired && empty($credentials[$fieldName])) {
                    $errors[$fieldName] = "The {$label} field is required.";
                }
            } 
            // If it's simple array (old format)
            else {
                $fieldName = $fieldValue;
                if (empty($credentials[$fieldName])) {
                    $errors[$fieldName] = "The {$fieldName} field is required.";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Make HTTP request with error handling and performance optimization
     */
    protected function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            // Use timeout from config for better performance
            $timeout = $this->defaultConfig['timeout'] ?? 30;
            $http = Http::timeout($timeout);
            // Respect SSL verification setting if provided by service defaultConfig
            if (isset($this->defaultConfig['verify_ssl']) && $this->defaultConfig['verify_ssl'] === false) {
                $http = $http->withoutVerifying();
            }
            
            // Add headers if provided
            if (isset($options['headers'])) {
                $http = $http->withHeaders($options['headers']);
                unset($options['headers']);
            }
            
            // Handle query parameters efficiently
            if (isset($options['query'])) {
                $url .= '?' . http_build_query($options['query'], '', '&', PHP_QUERY_RFC3986);
                unset($options['query']);
            }
            
            // Make request based on method with optimized handling
            $method = strtolower($method);
            if ($method === 'get') {
                $response = $http->get($url);
            } elseif ($method === 'post') {
                // Handle form_params or json efficiently
                if (isset($options['form_params'])) {
                    $response = $http->asForm()->post($url, $options['form_params']);
                } else {
                    $response = $http->post($url, $options);
                }
            } else {
                $response = $http->$method($url, $options);
            }
            
            // Optimize response handling
            $responseData = null;
            if ($response->successful()) {
                try {
                    $responseData = $response->json();
                } catch (\Exception $e) {
                    // Fallback to body if JSON parsing fails
                    $responseData = $response->body();
                }
            }
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $responseData,
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

