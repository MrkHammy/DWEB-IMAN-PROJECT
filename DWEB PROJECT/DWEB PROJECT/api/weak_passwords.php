<?php
/**
 * Fox Lab – Weak Passwords API
 * Returns the list of known compromised passwords as JSON
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT password FROM weak_passwords ORDER BY id ASC");
$passwords = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($passwords);
