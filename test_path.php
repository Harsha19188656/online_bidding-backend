<?php
$dir = __DIR__;
$path = __DIR__ . '/../../db.php';
$realPath = realpath($path);
echo "DIR: $dir\n";
echo "Path: $path\n";
echo "Real Path: $realPath\n";
echo "Exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
