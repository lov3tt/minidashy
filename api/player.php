<?php
// GET /api/player.php?id={id} — one player detail + full event history + flags.
// Called by Angular PlayerDetailComponent when a dashboard row is clicked.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/db.php';

// Cast to int immediately — never concatenate raw query params into SQL.
$playerId = (int) ($_GET['id'] ?? 0);
if ($playerId <= 0) {
    http_response_code(400); // malformed input
    echo json_encode(['error' => 'Missing or invalid id parameter']);
    exit;
}

// Prepared statement — named placeholder prevents SQL injection.
$stmt = $pdo->prepare("SELECT id, name, account_age_days FROM players WHERE id = :id");
$stmt->execute(['id' => $playerId]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$player) {
    http_response_code(404); // valid id format, player doesn't exist
    echo json_encode(['error' => 'Player not found']);
    exit;
}

// Full history newest first (players.php only returns the latest event).
$stmt = $pdo->prepare(
    "SELECT kills, deaths, accuracy, session_minutes, recorded_at
     FROM events WHERE player_id = :id ORDER BY recorded_at DESC"
);
$stmt->execute(['id' => $playerId]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// detectAnomalies() needs account_age_days, which lives on the player row
// not the event row — merge it in before calling.
$latestEvent = array_merge($events[0] ?? [], ['account_age_days' => $player['account_age_days']]);
$flags = $events ? detectAnomalies($pdo, $latestEvent) : [];

echo json_encode(['player' => $player, 'events' => $events, 'flags' => $flags]);