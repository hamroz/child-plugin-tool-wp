<?php
/**
 * Revert modifications made to the parent plugin
 *
 * This script reverts any modifications applied to the parent plugin directory
 * by the child plugin. It uses functions defined in src/core.php to perform
 * the revert operation.
 */

// Include core functions
require 'src/core.php';

// Set the operation type for logging
$output->operation = "revert";

// Call the core revert function
// The true parameter enables logging of the revert operation
revert(true);

// Output the results
require 'src/output.php';
