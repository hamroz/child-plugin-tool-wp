<?php
/**
 * Core functionality for the BookingPress Appointment Booking Pro Child plugin
 *
 * This file contains the core functions and configurations for the child plugin,
 * including path definitions, directory creation, and revert functionality.
 *
 * @package BookingPress
 * @subpackage BookingPress Appointment Booking Pro Child
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define paths
$currentDir = dirname(__FILE__);
$parentPluginDir = str_replace("-child/src", "", $currentDir);
$childPluginDir = str_replace("/src", "", $currentDir);
$modifiedDir = str_replace("/src", "/modifications", $currentDir);
$originalDir = str_replace("/src", "/original", $currentDir);
$removeDir = str_replace("/src", "/remove", $currentDir);
$newfilesDir = str_replace("/src", "/src/cache/newFiles", $currentDir);

// Remove placeholder files
$placeholderFiles = [
    $childPluginDir . "/modifications/delete_me.txt",
    $childPluginDir . "/original/delete_me.txt",
    $childPluginDir . "/src/cache/newFiles/delete_me.txt"
];
foreach ($placeholderFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

// Ensure core directories exist
$coreDirs = [
    $childPluginDir . "/original",
    $childPluginDir . "/modifications",
    $childPluginDir . "/src/cache/newFiles",
    $childPluginDir . "/remove"
];
foreach ($coreDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Determine folder positions for cleaner path outputs
$currentPaths = explode("/", $currentDir);
$pluginFolder = $currentPaths[count($currentPaths) - 3];
$pluginFolderPos = strpos($currentDir, $pluginFolder) + strlen($pluginFolder);

/**
 * Output result model class
 */
class OutputModel {
    public $operation; // 'apply' or 'revert'
    public $modfiedItems = []; // List of OuputItemModel for modified items
    public $originalItems = []; // List of OuputItemModel for original files
}

/**
 * Output item model class
 */
class OuputItemModel {
    public $fullPath; // Full path for file
    public $msg; // Message to display on the page
    public $color; // Status color: 'black', 'red', 'green'

    /**
     * Get the filename from the full path
     *
     * @return string The filename
     */
    function get_filename() {
        return basename($this->fullPath);
    }

    /**
     * Get the shortened path
     *
     * @return string The shortened path
     */
    function get_shortpath() {
        global $pluginFolderPos;
        return substr($this->fullPath, $pluginFolderPos);
    }
}

$output = new OutputModel();

/**
 * Get a list of files in a directory
 *
 * @param string $dir The directory to scan
 * @param bool $recurse Whether to recurse into subdirectories
 * @return array An array of file information
 */
function get_file_list($dir, $recurse = FALSE) {
    $retval = [];

    // Add trailing slash if missing
    if (substr($dir, -1) != "/") {
        $dir .= "/";
    }

    // Open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory {$dir} for reading");
    while (FALSE !== ($entry = $d->read())) {
        // Skip hidden files
        if ($entry[0] == ".") continue;
        if (is_dir("{$dir}{$entry}")) {
            $retval[] = [
                'name' => "{$dir}{$entry}/",
                'type' => filetype("{$dir}{$entry}"),
                'size' => 0,
                'lastmod' => filemtime("{$dir}{$entry}")
            ];
            if ($recurse && is_readable("{$dir}{$entry}/")) {
                $retval = array_merge($retval, get_file_list("{$dir}{$entry}/", TRUE));
            }
        } elseif (is_readable("{$dir}{$entry}")) {
            $retval[] = [
                'name' => "{$dir}{$entry}",
                'type' => mime_content_type("{$dir}{$entry}"),
                'size' => filesize("{$dir}{$entry}"),
                'lastmod' => filemtime("{$dir}{$entry}")
            ];
        }
    }
    $d->close();

    return $retval;
}

/**
 * Remove the server path from a full path
 *
 * @param string $path The full path
 * @return string The path without the server part
 */
function remove_server_path($path) {
    global $pluginFolderPos;
    return substr($path, $pluginFolderPos);
}

/**
 * Revert changes made to the parent plugin
 *
 * @param bool $enableLogging Whether to enable logging of the revert operation
 */
function revert($enableLogging = false) {
    global $originalDir, $parentPluginDir, $newfilesDir, $output, $removeDir;

    // Revert modified files
    foreach (get_file_list($originalDir, true) as $file) {
        if ($file['type'] == 'dir') continue;

        $modFile = $file['name'];
        $oldFile = str_replace("-child/original", "", $modFile);
        $originalFile = str_replace($parentPluginDir, $originalDir, $oldFile);

        $originalOutput = new OuputItemModel();
        $originalOutput->fullPath = $originalFile;
        $modifiedOuput = new OuputItemModel();
        $modifiedOuput->fullPath = $oldFile;

        copy($originalFile, $oldFile);
        $modifiedOuput->msg = "File reverted in parent directory";

        unlink($originalFile);
        $originalOutput->msg = "File deleted";

        if ($enableLogging) {
            $output->modfiedItems[] = $modifiedOuput;
            $output->originalItems[] = $originalOutput;
        }
    }

    // Remove added files
    foreach (get_file_list($newfilesDir, true) as $file) {
        if ($file['type'] == 'dir') continue;

        $newFile = $file['name'];
        $oldFile = str_replace("-child/src/cache/newFiles", "", $newFile);
        $modFile = str_replace("-child/src/cache/newFiles", "-child/modifications", $newFile);

        $modifiedOuput = new OuputItemModel();
        $modifiedOuput->fullPath = $oldFile;

        unlink($oldFile);
        unlink($newFile);
        $modifiedOuput->msg = "File removed from parent directory";

        if ($enableLogging) {
            $output->modfiedItems[] = $modifiedOuput;
        }
    }
}
