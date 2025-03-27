<?php

namespace SGS\Utility;

class File {
    public static function compress(string $filePath): void {
        $compressedFilePath = $filePath . '.gz';
        $data = file_get_contents($filePath);
        file_put_contents($compressedFilePath, gzencode($data, 9));
        unlink($filePath);
    }
}