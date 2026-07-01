<?php

/*

This file is the core engine
Not an endpoint — never outputs anything on its own. players.php and player.php both load it with require_once. It
provides the $pdo connection and three functions. The connection reads everything from environment variables so this
exact file works unmodified in both local Docker (reads from .env) and Render/Aiven production (reads from Render env
vars).

checkThresholdRule()

Fixed rule: accuracy >80% AND account under 14 days. Fast, auditable, but easy to evade by staying just under the threshold.

checkStatisticalRule()

Z-score of kills/min vs. population. Harder to game — adapts to the whole player base. Needs enough data to be meaningful.

detectAnomalies()

Orchestrator — runs both rules, collects whichever ones fired. Returns empty array for clean players.

*/


// api/db.php — shared: DB connection + all detection functions.
// NOT an endpoint. Loaded via require_once by players.php and player.php.

$host   = getenv('MYSQL_HOST') ?: 'db';  // 'db' = Docker service name fallback
$port   = getenv('MYSQL_PORT') ?: '3306';
$dbname = getenv('MYSQL_DATABASE');
$user   = getenv('MYSQL_USER');
$pass   = getenv('MYSQL_PASSWORD');
$useSSL = getenv('MYSQL_SSL') === 'true'; // off locally, 'true' on Render

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

// PHP 8.5 renamed the SSL constant. Check at runtime — works on any version.
if ($useSSL) {
    $sslConstant = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        ? Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT
        : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
    $options[$sslConstant] = false;
}

// ERRMODE_EXCEPTION makes failed queries throw PDOException,
// which our catch block converts to a clean JSON error response.
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Rule 1: Fixed threshold.
// Strength: instant, auditable. Weakness: trivial to evade by staying under 80%.
// Returns a flag reason string, or null if rule didn't fire.
function checkThresholdRule(array $event): ?string
{
    if ($event['accuracy'] > 80.0 && $event['account_age_days'] < 14) {
        return "Accuracy {$event['accuracy']}% on an account only "
             . "{$event['account_age_days']} days old (threshold: >80% under 14 days)";
    }
    return null;
}

// Rule 2: Population-relative z-score.
// Compares kills/min against the mean + stddev across ALL events.
// Strength: harder to game, adapts to the player base.
// Weakness: recomputes population stats on every call — fine at seed scale,
// would need caching in a real system.
function checkStatisticalRule(PDO $pdo, array $event): ?string
{
    $stmt = $pdo->query("SELECT kills, session_minutes FROM events");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert every row to kills/min so session length doesn't skew comparison.
    $rates = array_map(
        fn($r) => $r['session_minutes'] > 0 ? $r['kills'] / $r['session_minutes'] : 0,
        $rows
    );

    $mean = array_sum($rates) / count($rates);
    $variance = array_sum(array_map(fn($r) => ($r - $mean) ** 2, $rates)) / count($rates);
    $stddev = sqrt($variance);

    $playerRate = $event['session_minutes'] > 0
        ? $event['kills'] / $event['session_minutes'] : 0;

    if ($stddev == 0) return null; // guard: all rates identical

    $zScore = ($playerRate - $mean) / $stddev;

    if ($zScore > 3.0) {
        return sprintf(
            "Kill rate %.2f kills/min is %.1f standard deviations above the population mean (%.2f)",
            $playerRate, $zScore, $mean
        );
    }
    return null;
}

// Orchestrator: runs both rules, returns array of fired flag reasons.
// Empty array = clean. array_filter drops nulls, array_values re-indexes
// so json_encode outputs ["reason"] not {"1":"reason"}.
function detectAnomalies(PDO $pdo, array $event): array
{
    $flags = array_filter([
        checkThresholdRule($event),
        checkStatisticalRule($pdo, $event),
    ]);
    return array_values($flags);
}