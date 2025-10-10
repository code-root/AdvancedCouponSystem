<?php

namespace App\Services\Networks;

use Exception;

class NetworkServiceFactory
{
    /**
     * Create a network service instance based on network name
     * 
     * @param string $networkName
     * @return NetworkServiceInterface
     * @throws Exception
     */
    public static function create(string $networkName): NetworkServiceInterface
    {
        $serviceClass = self::getServiceClass($networkName);
        
        if (!class_exists($serviceClass)) {
            throw new Exception("Network service not found for: {$networkName}");
        }
        
        return new $serviceClass();
    }
    
    /**
     * Get service class name from network name
     * 
     * @param string $networkName
     * @return string
     */
    private static function getServiceClass(string $networkName): string
    {
        $className = ucfirst(strtolower($networkName)) . 'Service';
        return "App\\Services\\Networks\\{$className}";
    }
    
    /**
     * Check if a network service exists
     * 
     * @param string $networkName
     * @return bool
     */
    public static function exists(string $networkName): bool
    {
        $serviceClass = self::getServiceClass($networkName);
        return class_exists($serviceClass);
    }
    
    /**
     * Get all available network services
     * 
     * @return array
     */
    public static function getAllServices(): array
    {
        return [
            'boostiny' => BoostinyService::class,
            'clickdealer' => ClickDealerService::class,
            'admitad' => AdmitadService::class,
            'digizag' => DigizagService::class,
            'platformance' => PlatformanceService::class,
            'optimisemedia' => OptimiseMediaService::class,
            // Add more networks as needed
        ];
    }
}

