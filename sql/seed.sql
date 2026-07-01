/*
Normal (20 players)

Realistic accuracy 12–42%, kills/min ~0.2–0.4. Neither rule fires on these.

Threshold-only (4 players)

IDs 21–24. Accuracy >80% + account <14 days. Statistical rule does NOT catch these.

Statistical-only (2 players)

IDs 25–26. 5+ kills/min (z > 3.0), accuracy deliberately under 80%. Threshold rule does NOT catch these.

*/

SET NAMES utf8mb4;

INSERT INTO players (name, account_age_days) VALUES
    ('xX_Shadow_Xx', 412), ('PixelRunner99', 88), ('GhostBlade', 730),
    ('NoobMaster22', 14), ('SkySniper', 365), ('TacoTuesday', 200),
    ('FrostByte', 540), ('RogueAgent', 95), ('CrimsonWolf', 680),
    ('ViperStrike', 30), ('NightOwl_42', 410), ('IronFist', 220),
    ('SilentAssassin', 600), ('TurboTurtle', 75), ('BlazeRunner', 333),
    ('ThunderClap', 150), ('MysticArrow', 480), ('CobaltFlame', 60),
    ('SteelHawk', 290), ('VortexGamer', 18);

INSERT INTO players (name, account_age_days) VALUES
    ('NewAccount_777', 1), ('TooGoodToBeTrue', 5),
    ('SmurfSuspect', 3), ('AimAssistMaybe', 8),
    ('SpeedKill_Sus_A', 300), ('SpeedKill_Sus_B', 450);

-- Normal players (20 rows)
INSERT INTO events (player_id, kills, deaths, accuracy, session_minutes) VALUES
    (1,8,11,22.40,35),(2,4,9,18.10,22),(3,14,10,31.50,48),
    (4,2,8,14.30,15),(5,11,12,28.90,40),(6,6,10,19.70,25),
    (7,16,9,35.20,55),(8,5,11,17.60,20),(9,13,14,26.80,45),
    (10,3,7,15.90,18),(11,9,10,24.10,33),(12,7,9,21.30,28),
    (13,15,11,33.40,50),(14,4,8,16.50,19),(15,10,13,23.70,38),
    (16,6,9,20.20,24),(17,12,10,29.60,42),(18,5,10,18.80,21),
    (19,9,11,25.40,31),(20,8,12,22.90,29);

-- Threshold-only outliers: absurd accuracy + brand-new accounts
INSERT INTO events (player_id, kills, deaths, accuracy, session_minutes) VALUES
    (21,38,2,91.50,30),(22,45,1,95.20,25),
    (23,30,3,88.70,20),(24,50,0,97.80,35);

-- Statistical-only outliers: ~5 kills/min, accuracy UNDER 80% on purpose
INSERT INTO events (player_id, kills, deaths, accuracy, session_minutes) VALUES
    (25,40,5,42.00,8),(26,55,3,38.50,10);