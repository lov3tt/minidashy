<?php
// GET /api/players.php — all players with latest event + flag status.
// Called by Angular DashboardComponent on page load.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Answer preflight — browser sends OPTIONS before any cross-origin POST/JSON request.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET.']);
    exit;
}

require_once __DIR__ . '/db.php';

// Subquery picks the MAX(id) event per player = most recent session only.
// The dashboard shows current status, not full history (that's player.php).
$stmt = $pdo->query(
    "SELECT p.id, p.name, p.account_age_days,
            e.kills, e.deaths, e.accuracy, e.session_minutes
     FROM players p
     JOIN events e ON e.player_id = p.id
     WHERE e.id = (SELECT MAX(id) FROM events WHERE player_id = p.id)
     ORDER BY p.id"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cast types + run detection + add 'flagged' boolean for Angular to sort on.
$results = array_map(function ($row) use ($pdo) {
    $flags = detectAnomalies($pdo, $row);
    return [
        'id'               => (int) $row['id'],
        'name'             => $row['name'],
        'account_age_days' => (int) $row['account_age_days'],
        'kills'            => (int) $row['kills'],
        'deaths'           => (int) $row['deaths'],
        'accuracy'         => (float) $row['accuracy'],
        'session_minutes'  => (int) $row['session_minutes'],
        'flagged'          => count($flags) > 0,
        'flags'            => $flags,
    ];
}, $rows);

echo json_encode($results);