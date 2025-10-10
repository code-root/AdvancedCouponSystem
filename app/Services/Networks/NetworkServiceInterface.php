<?php

namespace App\Services\Networks;

interface NetworkServiceInterface
{
    /**
     * Test connection to the network API
     * 
     * @param array $credentials
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function testConnection(array $credentials): array;
    
    /**
     * Get required fields for this network
     * 
     * @return array
     */
    public function getRequiredFields(): array;
    
    /**
     * Get default configuration for this network
     * 
     * @return array
     */
    public function getDefaultConfig(): array;
    
    /**
     * Validate credentials
     * 
     * @param array $credentials
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateCredentials(array $credentials): array;
}

