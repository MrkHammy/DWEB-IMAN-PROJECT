<?php
/**
 * Fox Lab – Tutorials API
 * Returns tutorial lessons grouped by language as JSON
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT l.slug AS lang_slug, t.title, t.description, t.code FROM tutorials t JOIN languages l ON t.language_id = l.id ORDER BY l.slug ASC, t.display_order ASC");
$rows = $stmt->fetchAll();

$tutorials = [];
foreach ($rows as $row) {
    $lang = $row['lang_slug'];
    if (!isset($tutorials[$lang])) {
        $tutorials[$lang] = [];
    }
    $tutorials[$lang][] = [
        'title' => $row['title'],
        'desc'  => $row['description'],
        'code'  => $row['code']
    ];
}

echo json_encode($tutorials);
