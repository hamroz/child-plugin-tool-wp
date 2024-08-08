<?php
/**
 * Apply modifications to the parent plugin
 *
 * This script applies modifications from the child plugin to the parent plugin directory.
 * It handles updating existing files, creating new files, and removing files.
 */

require 'src/core.php';

$output->operation = "apply";

// Revert changes first to track new and deleted files
revert(false); // false turns off logging for revert operation

// Apply modifications to parent plugin directory
applyModifications();

// Remove files from parent plugin
removeFiles();

// Display results
require 'src/output.php';

/**
 * Apply modifications to files in the parent plugin directory
 */
function applyModifications() {
    global $output, $modifiedDir, $parentPluginDir, $originalDir;

    foreach (get_file_list($modifiedDir, true) as $file) {
        if ($file['type'] == 'dir') {
            continue; // Skip directories
        }

        $modFile = $file['name'];
        $oldFile = str_replace("-child/modifications", "", $modFile);
        $newFile = str_replace("-child/modifications", "-child/src/cache/newFiles", $modFile);
        $originalFile = str_replace($parentPluginDir, $originalDir, $oldFile);

        $originalOutput = new OuputItemModel();
        $originalOutput->fullPath = $originalFile;
        $modifiedOutput = new OuputItemModel();
        $modifiedOutput->fullPath = $oldFile;

        $operation = file_exists($oldFile) ? "update" : "create";

        if ($operation == "update") {
            backupOriginalFile($oldFile, $originalFile, $originalOutput);
        }

        ensureDirectoryExists(dirname($oldFile));

        // Copy modified file to parent plugin
        copy($modFile, $oldFile);
        $modifiedOutput->msg = "File " . ($operation == "update" ? "updated in" : "added to") . " parent directory";

        if ($operation == "create") {
            cacheNewFile($modFile, $newFile);
        }

        $output->modfiedItems[] = $modifiedOutput;
        if ($operation == "update") {
            $output->originalItems[] = $originalOutput;
        }
    }
}

/**
 * Remove files from the parent plugin
 */
function removeFiles() {
    global $output, $removeDir, $parentPluginDir, $originalDir;

    foreach (get_file_list($removeDir, true) as $file) {
        if ($file['type'] == 'dir') {
            continue; // Skip directories
        }

        $removeFile = $file['name'];
        $existingFile = str_replace("-child/remove", "", $removeFile);
        $originalFile = str_replace($parentPluginDir, $originalDir, $existingFile);

        $originalOutput = new OuputItemModel();
        $originalOutput->fullPath = $originalFile;
        $modifiedOutput = new OuputItemModel();
        $modifiedOutput->fullPath = $existingFile;

        if (!file_exists($originalFile) && file_exists($existingFile)) {
            ensureDirectoryExists(dirname($originalFile));
            rename($existingFile, $originalFile);
            $originalOutput->msg = "Copied file to original folder.";
            $modifiedOutput->msg = "File removed from plugin.";
        } else {
            $originalOutput->msg = "File already exists in original folder.";
            $modifiedOutput->msg = "File didn't exist in plugin.";
            $modifiedOutput->color = "red";
        }

        $output->originalItems[] = $originalOutput;
        $output->modfiedItems[] = $modifiedOutput;
    }
}

/**
 * Backup the original file before modification
 */
function backupOriginalFile($oldFile, $originalFile, &$originalOutput) {
    if (!file_exists($originalFile)) {
        ensureDirectoryExists(dirname($originalFile));
        rename($oldFile, $originalFile);
        $originalOutput->msg = "Moved file to original folder.";
    } else {
        $originalOutput->msg = "File already exists in original folder.";
    }
}

/**
 * Cache a new file for revert operations
 */
function cacheNewFile($modFile, $newFile) {
    ensureDirectoryExists(dirname($newFile));
    copy($modFile, $newFile);
}

/**
 * Ensure a directory exists, creating it if necessary
 */
function ensureDirectoryExists($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}
