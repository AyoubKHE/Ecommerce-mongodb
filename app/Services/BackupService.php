<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BackupService
{
    private static function copyFiles(string $sourceFolder, string $destinationFolder): bool
    {
        $files = Storage::files($sourceFolder);

        foreach ($files as $file) {

            $file_name_parts = explode("/", $file);
            $file_name = $file_name_parts[count($file_name_parts) - 1];

            $sourcePath = $sourceFolder . '/' . $file_name;
            $destinationPath = $destinationFolder . '/' . $file_name;
            if (!Storage::copy($sourcePath, $destinationPath)) {
                Storage::deleteDirectory($destinationFolder);
                return false;
            }
        }

        return true;
    }

    public static function createImagesBackup(string $tableName, string $id): bool
    {
        $sourceFolder = "public/" . $tableName . "/id_" . $id;
        $backupFolder = "public/" . $tableName . "/temp/id_" . $id;

        return self::copyFiles($sourceFolder, $backupFolder);
    }

    public static function deleteImagesBackup(string $tableName, string $id)
    {
        $backupFolder = "public/" . $tableName . "/temp/id_" . $id;
        Storage::deleteDirectory($backupFolder);
    }

    public static function makeImagesRestoration(string $tableName, string $id)
    {
        $backupFolder = "public/" . $tableName . "/temp/id_" . $id;
        $sourceFolder = "public/" . $tableName . "/id_" . $id;

        // supprimer l'ancien dossier
        Storage::deleteDirectory($sourceFolder);

        if (self::copyFiles($backupFolder, $sourceFolder)) {
            // supprimer le dossier de la sauvegarde
            Storage::deleteDirectory($backupFolder);
        }
    }
}
