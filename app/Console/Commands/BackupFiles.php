<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class BackupFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @var string
     */
    protected $signature = 'backup:files 
                            {--storage=local : Storage disk to use}
                            {--retention=30 : Number of days to retain backups}
                            {--include= : Comma-separated list of directories to include}
                            {--exclude= : Comma-separated list of directories to exclude}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a files backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting files backup...');

        try {
            $filename = $this->generateFilename();
            $filepath = $this->createFilesBackup($filename);
            
            $this->cleanOldBackups();
            
            $this->info("Files backup completed successfully!");
            $this->info("Backup saved to: {$filepath}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Files backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create files backup.
     */
    private function createFilesBackup(string $filename): string
    {
        $storage = $this->option('storage');
        $disk = Storage::disk($storage);
        
        // Create backups directory if it doesn't exist
        if (!$disk->exists('backups')) {
            $disk->makeDirectory('backups');
        }
        
        $filepath = "backups/{$filename}";
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Create temp directory
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        // Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception("Cannot create ZIP file: {$tempPath}");
        }
        
        // Add files to archive
        $this->addFilesToArchive($zip);
        
        $zip->close();
        
        // Upload to storage
        $disk->put($filepath, file_get_contents($tempPath));
        
        // Clean up temp file
        unlink($tempPath);
        
        return $filepath;
    }

    /**
     * Add files to ZIP archive.
     */
    private function addFilesToArchive(ZipArchive $zip): void
    {
        $basePath = base_path();
        $includeDirs = $this->getIncludeDirectories();
        $excludeDirs = $this->getExcludeDirectories();
        
        foreach ($includeDirs as $dir) {
            $fullPath = $basePath . '/' . $dir;
            
            if (is_dir($fullPath)) {
                $this->addDirectoryToArchive($zip, $fullPath, $dir, $excludeDirs);
            }
        }
    }

    /**
     * Add directory to ZIP archive.
     */
    private function addDirectoryToArchive(ZipArchive $zip, string $fullPath, string $relativePath, array $excludeDirs): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativeFilePath = $relativePath . '/' . $iterator->getSubPathName();
            
            // Skip excluded directories
            if ($this->shouldExclude($relativeFilePath, $excludeDirs)) {
                continue;
            }
            
            // Skip large files and unnecessary files
            if ($this->shouldSkipFile($filePath)) {
                continue;
            }
            
            if ($file->isFile()) {
                $zip->addFile($filePath, $relativeFilePath);
                $this->line("Added: {$relativeFilePath}");
            }
        }
    }

    /**
     * Check if file should be excluded.
     */
    private function shouldExclude(string $filePath, array $excludeDirs): bool
    {
        foreach ($excludeDirs as $excludeDir) {
            if (str_starts_with($filePath, $excludeDir)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if file should be skipped.
     */
    private function shouldSkipFile(string $filePath): bool
    {
        // Skip files larger than 100MB
        if (filesize($filePath) > 100 * 1024 * 1024) {
            return true;
        }
        
        // Skip unnecessary file types
        $skipExtensions = ['.log', '.tmp', '.cache', '.pid', '.lock'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (in_array('.' . $extension, $skipExtensions)) {
            return true;
        }
        
        // Skip node_modules and vendor directories
        if (str_contains($filePath, 'node_modules') || str_contains($filePath, 'vendor')) {
            return true;
        }
        
        return false;
    }

    /**
     * Get directories to include in backup.
     */
    private function getIncludeDirectories(): array
    {
        $include = $this->option('include');
        
        if ($include) {
            return array_map('trim', explode(',', $include));
        }
        
        // Default directories to backup
        return [
            'app',
            'config',
            'database',
            'resources',
            'routes',
            'public',
            'storage/app/public',
        ];
    }

    /**
     * Get directories to exclude from backup.
     */
    private function getExcludeDirectories(): array
    {
        $exclude = $this->option('exclude');
        
        if ($exclude) {
            return array_map('trim', explode(',', $exclude));
        }
        
        // Default directories to exclude
        return [
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'bootstrap/cache',
            'node_modules',
            'vendor',
            '.git',
        ];
    }

    /**
     * Generate backup filename.
     */
    private function generateFilename(): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        return "files_backup_{$timestamp}.zip";
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
            if (str_contains($file, 'files_backup_')) {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                
                if ($lastModified->isBefore($cutoffDate)) {
                    $disk->delete($file);
                    $this->info("Deleted old backup: {$file}");
                }
            }
        }
    }
}