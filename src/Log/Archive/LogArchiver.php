<?php declare(strict_types=1);

namespace SGS\Log\Archive;

use ZipArchive;
use RuntimeException;

class LogArchiver {
    public function __construct(private string $logsPath, private string $archivePath, private int $maxAgeDays = 30) {
        $this->ensureDirectoryExists($archivePath);
    }

    public function archive(): int {
        $cutoff = time() - ($this->maxAgeDays * 24 * 60 * 60);
        $archived = 0;

        foreach (glob($this->logsPath.'/*.log*') as $logFile) {
            if (filemtime($logFile) < $cutoff) {
                $this->compress($logFile);
                unlink($logFile);
                $archived++;
            }
        }

        return $archived;
    }

    private function compress(string $file): void {
        $archiveName = $this->archivePath.'/'.basename($file).'.zip';

        $zip = new ZipArchive();
        if ($zip->open($archiveName, ZipArchive::CREATE) !== true) {
            throw new RuntimeException("Failed to create archive: {$archiveName}");
        }

        $zip->addFile($file, basename($file));
        $zip->close();
    }

    private function ensureDirectoryExists(string $path): void {
        if (!file_exists($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException("Failed to create archive directory: {$path}");
        }
    }
}