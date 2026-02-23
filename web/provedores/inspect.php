<?php
// Show exact line 59 of index.php
$lines = file('index.php');
echo "Total lines: " . count($lines) . "\n";
echo "Line 59: " . (isset($lines[58]) ? htmlspecialchars($lines[58]) : 'DOES NOT EXIST') . "\n";
echo "MD5: " . md5_file('index.php') . "\n";
echo "Size: " . filesize('index.php') . " bytes\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime('index.php')) . "\n";
?>
