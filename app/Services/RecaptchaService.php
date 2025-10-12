<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    // OmoCaptcha API configuration
    private const CLIENT_KEY = 'OMO_IIVGP6TSCKOANJDRBOTZWKJJMS2JIHOTFJAKDVRENA1GD2GVHSF6GULCPFNSIZ1756818806';
    private const CREATE_URL = 'https://api.omocaptcha.com/v2/createTask';
    private const RESULT_URL = 'https://api.omocaptcha.com/v2/getTaskResult';
    
    private const POLL_INTERVAL_SECONDS = 3;
    private const MAX_WAIT_SECONDS = 45;
    
    /**
     * Solve reCAPTCHA v3 token
     */
    public function solveRecaptchaV3(string $websiteUrl, string $websiteKey, string $pageAction = 'myverify', float $minScore = 0.3): ?string
    {
        try {
            // Step 1: Create task
            $taskId = $this->createTask($websiteUrl, $websiteKey, $pageAction, $minScore);
            
            if (empty($taskId)) {
                Log::error('RecaptchaService: Failed to create task');
                return null;
            }
            
            Log::info("RecaptchaService: Task created with ID: {$taskId}");
            
            // Step 2: Poll for result
            $solution = $this->pollForResult($taskId);
            
            return $solution;
            
        } catch (\Exception $e) {
            Log::error('RecaptchaService: Exception - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create captcha solving task
     */
    private function createTask(string $websiteUrl, string $websiteKey, string $pageAction, float $minScore): ?string
    {
        try {
            $payload = [
                'clientKey' => self::CLIENT_KEY,
                'task' => [
                    'type' => 'RecaptchaV3TokenTask',
                    'websiteURL' => $websiteUrl,
                    'websiteKey' => $websiteKey,
                    'minScore' => $minScore,
                    'pageAction' => $pageAction
                ]
            ];
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])->post(self::CREATE_URL, $payload);
            
            if (!$response->successful()) {
                Log::error('RecaptchaService: Create task failed with status ' . $response->status());
                return null;
            }
            
            $data = $response->json();
            
            // Check for errors
            if (isset($data['errorId']) && $data['errorId'] != 0) {
                Log::error('RecaptchaService: API error - ' . ($data['errorCode'] ?? 'Unknown'));
                return null;
            }
            
            return $data['taskId'] ?? null;
            
        } catch (\Exception $e) {
            Log::error('RecaptchaService: Create task exception - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Poll for task result
     */
    private function pollForResult(string $taskId): ?string
    {
        $startTime = now();
        $timeoutAt = $startTime->addSeconds(self::MAX_WAIT_SECONDS);
        $attempt = 0;
        
        while (now() <= $timeoutAt) {
            $attempt++;
            Log::info("RecaptchaService: Polling attempt #{$attempt} for task {$taskId}");
            
            try {
                $payload = [
                    'clientKey' => self::CLIENT_KEY,
                    'taskId' => $taskId
                ];
                
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])->post(self::RESULT_URL, $payload);
                
                if (!$response->successful()) {
                    Log::warning('RecaptchaService: Get result failed with status ' . $response->status());
                    sleep(self::POLL_INTERVAL_SECONDS);
                    continue;
                }
                
                $data = $response->json();
                
                // Check for errors
                if (isset($data['errorId']) && $data['errorId'] != 0) {
                    Log::error('RecaptchaService: API error - ' . ($data['errorCode'] ?? 'Unknown'));
                    return null;
                }
                
                $status = $data['status'] ?? '';
                
                if ($status === 'processing') {
                    Log::info('RecaptchaService: Still processing...');
                    sleep(self::POLL_INTERVAL_SECONDS);
                    continue;
                }
                
                if ($status === 'ready') {
                    if (isset($data['solution']['gRecaptchaResponse'])) {
                        $token = $data['solution']['gRecaptchaResponse'];
                        Log::info('RecaptchaService: Solution ready!');
                        return $token;
                    } else {
                        Log::error('RecaptchaService: Solution ready but no gRecaptchaResponse found');
                        return null;
                    }
                }
                
                Log::error('RecaptchaService: Unexpected status - ' . $status);
                return null;
                
            } catch (\Exception $e) {
                Log::error('RecaptchaService: Polling exception - ' . $e->getMessage());
                sleep(self::POLL_INTERVAL_SECONDS);
            }
        }
        
        Log::error('RecaptchaService: Timeout - No solution within ' . self::MAX_WAIT_SECONDS . ' seconds');
        return null;
    }
}

