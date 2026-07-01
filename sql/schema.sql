/*
Table design decisions

players holds static identity (name, account age). events holds behavioral data — one row per session. 
The FOREIGN KEY from events.player_id → players.id enforces referential integrity: 
you can't insert an event for a player that doesn't exist. 
The INDEX idx_player_id speeds up the frequent "get all events for this player" query. 
utf8mb4 is required (not utf8) because standard MySQL utf8 is 3-byte and can't store emoji.

*/

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    account_age_days INT NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    kills INT NOT NULL,
    deaths INT NOT NULL,
    accuracy DECIMAL(5,2) NOT NULL,
    session_minutes INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id),
    INDEX idx_player_id (player_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;