<?php
require_once 'vendor/autoload.php';

echo "Testing Firebase API...\n";

try {
    // Check if ServiceAccount class exists
    if (class_exists('Kreait\Firebase\ServiceAccount')) {
        echo "✅ ServiceAccount class exists\n";
        
        $rc = new ReflectionClass('Kreait\Firebase\ServiceAccount');
        $methods = $rc->getMethods();
        
        echo "Available methods:\n";
        foreach ($methods as $method) {
            echo "- " . $method->getName() . "\n";
        }
        
    } else {
        echo "❌ ServiceAccount class not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
