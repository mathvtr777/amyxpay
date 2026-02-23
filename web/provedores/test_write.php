<?php
$target = __DIR__ . '/index.php';
$before_md5 = md5_file($target);
$testContent = "<?php echo 'REWRITTEN - ' . date('H:i:s'); ?>";
$r = file_put_contents($target, $testContent);
$after_md5 = md5_file($target);
echo "target: $target\n";
echo "result: " . ($r !== false ? "OK bytes=$r" : "FAILED") . "\n";
echo "md5_before: $before_md5\n";
echo "md5_after:  $after_md5\n";
echo "changed: " . ($before_md5 !== $after_md5 ? "YES" : "NO") . "\n";
// Do NOT unlink - need to see output
