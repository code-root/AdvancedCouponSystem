<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Networks\PlatformanceService;
use Illuminate\Support\Facades\Http;

class PlatformanceServiceTest extends TestCase
{
    protected PlatformanceService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlatformanceService();
    }
    
    /**
     * Test connection with real credentials
     * 
     * @return void
     */
    public function test_platformance_connection_with_real_credentials()
    {
        // Real credentials - replace with your actual credentials
        $credentials = [
            'email' => 'haron.ali10@gmail.com',
            'password' => '12345678', // Plain password (will be encoded in service)
        ];
        
        echo "\n\n";
        echo "========================================\n";
        echo "Testing Platformance Connection\n";
        echo "========================================\n";
        echo "Email: " . $credentials['email'] . "\n";
        echo "Testing connection...\n\n";
        
        // Test connection
        $result = $this->service->testConnection($credentials);
        
        // Display results
        echo "========================================\n";
        echo "Connection Result:\n";
        echo "========================================\n";
        echo "Success: " . ($result['success'] ? 'YES ✓' : 'NO ✗') . "\n";
        echo "Message: " . $result['message'] . "\n";
        
        if ($result['success'] && isset($result['data'])) {
            echo "\n--- Connection Data ---\n";
            echo "Token: " . ($result['data']['token'] ?? 'N/A') . "\n";
            echo "PHPSESSID: " . ($result['data']['phpsessid'] ?? 'N/A') . "\n";
            echo "Cookies: " . substr($result['data']['cookies'] ?? 'N/A', 0, 100) . "...\n";
            
            if (isset($result['data']['all_cookies'])) {
                echo "\n--- All Cookies ---\n";
                foreach ($result['data']['all_cookies'] as $cookie) {
                    $name = $cookie['Name'] ?? 'unknown';
                    $value = substr($cookie['Value'] ?? '', 0, 50);
                    echo "  {$name}: {$value}...\n";
                }
            }
        }
        
        echo "\n========================================\n\n";
        
        // Assert
        $this->assertTrue($result['success'], 'Connection should be successful');
        $this->assertArrayHasKey('data', $result, 'Result should contain data');
        $this->assertArrayHasKey('token', $result['data'], 'Data should contain token');
        $this->assertArrayHasKey('phpsessid', $result['data'], 'Data should contain phpsessid');
        $this->assertArrayHasKey('cookies', $result['data'], 'Data should contain cookies');
    }
    
    /**
     * Test sync data with stored cookies
     * 
     * @return void
     */
    public function test_platformance_sync_data()
    {
        // First, get connection
        $credentials = [
            'email' => 'haron.ali10@gmail.com',
            'password' => '12345678',
        ];
        
        echo "\n\n";
        echo "========================================\n";
        echo "Testing Platformance Data Sync\n";
        echo "========================================\n";
        
        $connectionResult = $this->service->testConnection($credentials);
        
        if (!$connectionResult['success']) {
            echo "Connection failed: " . $connectionResult['message'] . "\n";
            $this->markTestSkipped('Connection failed, skipping sync test');
            return;
        }
        
        echo "Connection successful!\n";
        echo "Fetching data...\n\n";
        
        // Now test sync with the cookies
        $syncCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'token' => $connectionResult['data']['token'],
            'phpsessid' => $connectionResult['data']['phpsessid'],
            'cookies' => $connectionResult['data']['cookies'],
        ];
        
        echo "\n--- Stored Credentials for Sync ---\n";
        echo "Token: " . $syncCredentials['token'] . "\n";
        echo "PHPSESSID: " . $syncCredentials['phpsessid'] . "\n";
        echo "Cookies: " . $syncCredentials['cookies'] . "\n";
        
        // Test with wider date range (last 3 months)
        $startDate = date('Y-m-d', strtotime('-3 months')); // 3 months ago
        $endDate = date('Y-m-d'); // Today
        
        echo "\nDate Range: {$startDate} to {$endDate}\n\n";
        
        $syncResult = $this->service->syncData($syncCredentials, $startDate, $endDate);
        
        // Display results
        echo "========================================\n";
        echo "Sync Result:\n";
        echo "========================================\n";
        echo "Success: " . ($syncResult['success'] ? 'YES ✓' : 'NO ✗') . "\n";
        echo "Message: " . $syncResult['message'] . "\n";
        
        if (isset($syncResult['raw_html_length'])) {
            echo "HTML Response Length: " . $syncResult['raw_html_length'] . " bytes\n";
        }
        
        if (isset($syncResult['html_preview'])) {
            echo "\n--- HTML Preview ---\n";
            echo $syncResult['html_preview'] . "\n";
            echo "...\n";
        }
        
        if ($syncResult['success'] && isset($syncResult['data']['coupons'])) {
            $coupons = $syncResult['data']['coupons'];
            echo "\nTotal Records: " . count($coupons) . "\n";
            
            if (count($coupons) > 0) {
                echo "\n--- Sample Data (First 5 records) ---\n";
                foreach (array_slice($coupons, 0, 5) as $index => $coupon) {
                    echo "\nRecord #" . ($index + 1) . ":\n";
                    echo "  Campaign: " . ($coupon['campaign_name'] ?? 'N/A') . "\n";
                    echo "  Campaign ID: " . ($coupon['campaign_id'] ?? 'N/A') . "\n";
                    echo "  Code: " . ($coupon['code'] ?? 'N/A') . "\n";
                    echo "  Revenue: $" . ($coupon['revenue'] ?? 0) . "\n";
                    echo "  Order Value: $" . ($coupon['order_value'] ?? 0) . "\n";
                    echo "  Quantity: " . ($coupon['quantity'] ?? 0) . "\n";
                    echo "  Date: " . ($coupon['order_date'] ?? 'N/A') . "\n";
                }
                
                // Calculate totals
                $totalRevenue = array_sum(array_column($coupons, 'revenue'));
                $totalOrders = array_sum(array_column($coupons, 'quantity'));
                
                echo "\n--- Summary ---\n";
                echo "Total Revenue: $" . number_format($totalRevenue, 2) . "\n";
                echo "Total Orders: " . $totalOrders . "\n";
            }
        }
        
        echo "\n========================================\n\n";
        
        // Assert
        $this->assertTrue($syncResult['success'], 'Sync should be successful');
        $this->assertArrayHasKey('data', $syncResult, 'Result should contain data');
    }
    
    /**
     * Test with invalid credentials
     * 
     * @return void
     */
    public function test_platformance_connection_with_invalid_credentials()
    {
        $credentials = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ];
        
        echo "\n\n";
        echo "========================================\n";
        echo "Testing Invalid Credentials\n";
        echo "========================================\n";
        
        $result = $this->service->testConnection($credentials);
        
        echo "Success: " . ($result['success'] ? 'YES' : 'NO ✓') . "\n";
        echo "Message: " . $result['message'] . "\n";
        echo "========================================\n\n";
        
        // Assert
        $this->assertFalse($result['success'], 'Connection should fail with invalid credentials');
    }
}
