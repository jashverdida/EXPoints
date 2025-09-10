<?php
// Autoload composer dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die('Composer dependencies not installed. Please run composer install.');
}
?>
