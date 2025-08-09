<?php
// Shared helper to autoload classes in includes/ directory
spl_autoload_register(function ($className) {
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
    $candidate = $baseDir . $className . '.php';
    if (is_file($candidate)) {
        require_once $candidate;
    }
});
?>