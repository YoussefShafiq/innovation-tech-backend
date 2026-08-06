<?php

namespace App\Helpers;

class StorageHelper
{
    /**
     * Web-accessible destinations for a relative storage path.
     * Primary: public/storage (served by artisan / typical Laravel).
     * Legacy: project-root storage/ (used by older deploys).
     *
     * @param string $filePath relative path like 'settings/filename.jpg'
     * @return array<int, string>
     */
    private static function publicDestinations(string $filePath): array
    {
        return [
            public_path('storage/' . $filePath),
            base_path('storage/' . $filePath),
        ];
    }

    /**
     * Sync a file from storage/app/public to web-accessible storage
     *
     * @param string $filePath - relative path like 'services/covers/filename.jpg'
     * @return bool
     */
    public static function syncToPublic($filePath)
    {
        $source = storage_path('app/public/' . $filePath);

        if (!file_exists($source)) {
            return false;
        }

        $copied = false;
        foreach (self::publicDestinations($filePath) as $destination) {
            $destinationDir = dirname($destination);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }
            if (copy($source, $destination)) {
                $copied = true;
            }
        }

        return $copied;
    }

    /**
     * Sync entire directory structure
     *
     * @param string $directory - like 'services'
     * @return int number of files synced
     */
    public static function syncDirectory($directory = '')
    {
        $source = storage_path('app/public/' . $directory);

        if (!is_dir($source)) {
            return 0;
        }

        $synced = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $relativePath = ltrim(str_replace('\\', '/', substr($file->getRealPath(), strlen(realpath($source)))), '/');
            $relativeFile = $directory !== ''
                ? trim($directory, '/') . '/' . $relativePath
                : $relativePath;

            if (self::syncToPublic($relativeFile)) {
                $synced++;
            }
        }

        return $synced;
    }

    public static function deleteFromDirectory($filePath)
    {
        $deleted = false;

        $paths = array_merge(
            [
                storage_path('app/public/' . $filePath),
                storage_path('app/' . $filePath),
            ],
            self::publicDestinations($filePath)
        );

        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                if (@unlink($path)) {
                    $deleted = true;
                }
            }
        }

        return $deleted;
    }
}
