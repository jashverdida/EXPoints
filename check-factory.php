<?php
require_once 'vendor/autoload.php';

$rc = new ReflectionClass('Kreait\Firebase\Factory');
$methods = $rc->getMethods();

echo "Available methods in Factory:\n";
foreach ($methods as $method) {
    echo "- " . $method->getName() . "\n";
}
?>
