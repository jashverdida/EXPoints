<?php
/**
 * Test EXP and Level Calculations
 */

require_once 'includes/ExpSystem.php';

echo "=== EXP TO LEVEL CONVERSION TEST ===\n\n";

$testCases = [
    ['exp' => 0, 'expected_level' => 1],
    ['exp' => 1, 'expected_level' => 2],
    ['exp' => 5, 'expected_level' => 2],
    ['exp' => 10, 'expected_level' => 2],
    ['exp' => 11, 'expected_level' => 3],
    ['exp' => 20, 'expected_level' => 3],
    ['exp' => 21, 'expected_level' => 4],
    ['exp' => 25, 'expected_level' => 4],
    ['exp' => 30, 'expected_level' => 4],
    ['exp' => 31, 'expected_level' => 5],
    ['exp' => 50, 'expected_level' => 6],
    ['exp' => 100, 'expected_level' => 11],
];

echo "Testing level calculations:\n\n";
echo "╔═══════════╦═══════════════╦═════════════╗\n";
echo "║ EXP       ║ Expected Lvl  ║ Actual Lvl  ║\n";
echo "╠═══════════╬═══════════════╬═════════════╣\n";

$allPassed = true;

foreach ($testCases as $test) {
    $calculatedLevel = ExpSystem::calculateLevel($test['exp']);
    $status = ($calculatedLevel === $test['expected_level']) ? '✓' : '✗';
    
    if ($calculatedLevel !== $test['expected_level']) {
        $allPassed = false;
    }
    
    printf("║ %-9s ║ %-13s ║ %-11s %s║\n",
        $test['exp'],
        $test['expected_level'],
        $calculatedLevel,
        $status
    );
}

echo "╚═══════════╩═══════════════╩═════════════╝\n\n";

if ($allPassed) {
    echo "✅ All tests PASSED!\n\n";
} else {
    echo "❌ Some tests FAILED!\n\n";
}

echo "=== LIKE TO EXP CONVERSION ===\n\n";
echo "Likes → EXP → Level:\n";
echo "  1 like  = 5 EXP   = Level 2\n";
echo "  2 likes = 10 EXP  = Level 2\n";
echo "  3 likes = 15 EXP  = Level 3\n";
echo "  5 likes = 25 EXP  = Level 4\n";
echo "  7 likes = 35 EXP  = Level 5\n";
echo "  10 likes = 50 EXP = Level 6\n";
echo "  20 likes = 100 EXP = Level 11\n\n";

echo "=== LEVEL PROGRESSION TABLE ===\n\n";
echo "╔═══════╦═════════════╦═══════════╦═════════════╗\n";
echo "║ Level ║ EXP Needed  ║ Total EXP ║ Likes Needed║\n";
echo "╠═══════╬═════════════╬═══════════╬═════════════╣\n";

for ($level = 1; $level <= 10; $level++) {
    if ($level === 1) {
        $totalExp = 0;
    } elseif ($level === 2) {
        $totalExp = 1;
    } else {
        $totalExp = 1 + ($level - 2) * 10;
    }
    
    $expNeeded = ($level === 1) ? 0 : (($level === 2) ? 1 : 10);
    $likesNeeded = ceil($totalExp / 5);
    
    printf("║ %-5s ║ %-11s ║ %-9s ║ %-11s ║\n",
        $level,
        $expNeeded,
        $totalExp,
        $likesNeeded
    );
}

echo "╚═══════╩═════════════╩═══════════╩═════════════╝\n";
?>
