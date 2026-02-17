<?php
/**
 * Fox Lab – Search Terms API
 * Returns matching glossary terms with brief definitions for autocomplete.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT id, title, category, LEFT(definition, 120) AS brief
        FROM terms
        WHERE title LIKE :q
        ORDER BY
            CASE WHEN title LIKE :exact THEN 0 ELSE 1 END,
            title ASC
        LIMIT 8
    ");
    $stmt->execute([
        ':q'     => '%' . $query . '%',
        ':exact' => $query . '%'
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Append ellipsis to truncated definitions
    foreach ($results as &$row) {
        if (strlen($row['brief']) >= 117) {
            $row['brief'] = rtrim($row['brief']) . '...';
        }
    }

    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
