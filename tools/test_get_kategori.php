<?php
// Test runner for app/ajax/get_kategori.php
$_GET['sukan_id'] = isset($argv[1]) ? $argv[1] : 1;
require_once __DIR__ . '/../app/ajax/get_kategori.php';
