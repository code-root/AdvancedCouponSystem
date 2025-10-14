<?php

declare(strict_types=1);

require __DIR__ . '/CLI.php';

use App\Services\Networks\Omolaat\CLI;

/**
 * Optimized CLI script for Omolaat data fetching
 * Uses direct function calls instead of shell_exec for better performance
 * 
 * Usage:
 *   php cli_optimized.php --email=... --password=... --max_pages=100 \
 *                         [--output=json|csv] [--out=filename] \
 *                         [--from=YYYY-MM-DD|ms] [--to=YYYY-MM-DD|ms]
 */

// Parse command line options
$opts = getopt('', [
    'email:', 'password:', 'max_pages::', 'output::', 'out::', 'from::', 'to::'
]);

// Validate required options
if (!isset($opts['email'], $opts['password'])) {
    fwrite(STDERR, json_encode([
        'success' => false,
        'message' => 'Missing --email or --password'
    ], JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}

// Set default values
$options = [
    'email' => (string) $opts['email'],
    'password' => (string) $opts['password'],
    'max_pages' => isset($opts['max_pages']) ? (int) $opts['max_pages'] : 100,
    'output' => isset($opts['output']) ? strtolower((string) $opts['output']) : 'json',
    'out' => isset($opts['out']) ? (string) $opts['out'] : null,
    'from' => isset($opts['from']) ? (string) $opts['from'] : null,
    'to' => isset($opts['to']) ? (string) $opts['to'] : null,
];

// Validate output format
if (!in_array($options['output'], ['json', 'csv'], true)) {
    $options['output'] = 'json';
}

try {
    // Execute CLI with optimized performance
    $cli = new CLI();
    $result = $cli->execute($options);

    // Output result
    $output = json_encode($result, JSON_UNESCAPED_UNICODE);
    
    if ($result['success'] && isset($options['out']) && $options['output'] === 'json') {
        // Write to file
        $dir = dirname($options['out']);
        if ($dir !== '.' && !is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot create directory: ' . $dir
                ], JSON_UNESCAPED_UNICODE) . "\n";
                exit(1);
            }
        }
        
        if (file_put_contents($options['out'], $output . "\n") === false) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to write file: ' . $options['out']
            ], JSON_UNESCAPED_UNICODE) . "\n";
            exit(1);
        }
        
        echo json_encode([
            'success' => true,
            'file' => $options['out']
        ], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        // Output to stdout
        echo $output . "\n";
    }
    
    exit($result['success'] ? 0 : 1);
    
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}
