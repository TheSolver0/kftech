<?php
require_once __DIR__ . '/config/api.php';

// Tester avec quelques IDs
$testIds = [1, 2, 3, 16];

foreach ($testIds as $id) {
    echo "\n=== Testing Product ID: $id ===\n";
    $result = apiGet('/products/' . $id);
    echo "Response type: " . gettype($result) . "\n";
    echo "Response is empty: " . (empty($result) ? 'YES' : 'NO') . "\n";
    echo "Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "---\n";
}

// Test API recent products
echo "\n\n=== Testing Recent Products ===\n";
$recent = apiGet('/products?limit=5');
echo "Response:\n";
echo json_encode($recent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
