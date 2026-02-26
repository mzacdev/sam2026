<?php
require 'd:/WWW/e-sports/app/config/database.php';
$db = getDB();

echo "[COLUMNS table_round]\n";
$cols = $db->query('SHOW COLUMNS FROM table_round')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) { echo $c['Field'] . "\n"; }

echo "\n[SPORTS]\n";
$sports = $db->query('SELECT id, nama_sukan FROM table_sukan WHERE deleted_at IS NULL ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($sports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n[ROUNDS]\n";
$rounds = $db->query('SELECT id, nama_round, round_type, round_order, group_code, status, is_locked, event_id, sukan_id FROM table_round WHERE deleted_at IS NULL ORDER BY round_order, group_code, id')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rounds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
?>
