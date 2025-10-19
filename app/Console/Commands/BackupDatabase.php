<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--compress : Compress the backup file}
                            {--storage=local : Storage disk to use}
                            {--retention=30 : Number of days to retain backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        try {
            $backupData = $this->createBackup();
            $filename = $this->generateFilename();
            $filepath = $this->saveBackup($backupData, $filename);
            
            $this->cleanOldBackups();
            
            $this->info("Database backup completed successfully!");
            $this->info("Backup saved to: {$filepath}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Database backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create database backup.
     */
    private function createBackup(): string
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        // Create mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database)
        );

        // Execute command and capture output
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("mysqldump failed with return code: {$returnCode}");
        }

        return implode("\n", $output);
    }

    /**
     * Generate backup filename.
     */
    private function generateFilename(): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $extension = $this->option('compress') ? 'sql.gz' : 'sql';
        
        return "database_backup_{$timestamp}.{$extension}";
    }

    /**
     * Save backup to storage.
     */
    private function saveBackup(string $backupData, string $filename): string
    {
        $storage = $this->option('storage');
        $disk = Storage::disk($storage);
        
        // Create backups directory if it doesn't exist
        if (!$disk->exists('backups')) {
            $disk->makeDirectory('backups');
        }
        
        $filepath = "backups/{$filename}";
        
        if ($this->option('compress')) {
            $compressedData = gzencode($backupData, 9);
            $disk->put($filepath, $compressedData);
        } else {
            $disk->put($filepath, $backupData);
        }
        
        return $filepath;
    }

    /**
     * Clean old backups.
     */
    private function cleanOldBackups(): void
    {
        $retentionDays = (int) $this->option('retention');
        $storage = $this->option('storage');
        $disk = Storage::disk($storage);
        
        if (!$disk->exists('backups')) {
            return;
        }
        
        $files = $disk->files('backups');
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        foreach ($files as $file) {
            if (str_contains($file, 'database_backup_')) {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                
                if ($lastModified->isBefore($cutoffDate)) {
                    $disk->delete($file);
                    $this->info("Deleted old backup: {$file}");
                }
            }
        }
    }
}