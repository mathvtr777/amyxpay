<?php
// Read index.php AFTER test_write ran
$target = __DIR__ . '/index.php';
$content = file_get_contents($target);
echo "size: " . strlen($content) . "\n";
echo "first 100 chars: " . htmlspecialchars(substr($content, 0, 100)) . "\n";
echo "md5: " . md5($content) . "\n";
// Also check if index.php is the same as a symlink somewhere
echo "realpath: " . realpath($target) . "\n";
