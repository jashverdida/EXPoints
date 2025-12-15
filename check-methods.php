<?php
require_once 'vendor/autoload.php';

$rc = new ReflectionClass('Kreait\Firebase\ServiceAccount');
$methods = $rc->getMethods();

echo "Available methods in ServiceAccount:\n";
foreach ($methods as $method) {
    echo "- " . $method->getName() . "\n";
}
?>
