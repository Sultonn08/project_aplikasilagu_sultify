-- ============================================================
--  SULTIFY - Music Streaming Platform
--  Database: sultify
--  Author: Sulton
--  Version: 1.0.0
--  Engine: MySQL 8.x
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- ============================================================
-- Buat & Gunakan Database
-- ============================================================
CREATE DATABASE IF NOT EXISTS `sultify`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sultify`;

-- ============================================================
-- Drop tabel lama (urutan terbalik karena FK)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `history`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `playlist_songs`;
DROP TABLE IF EXISTS `playlists`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `albums`;
DROP TABLE IF EXISTS `artists`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. Tabel: admins
-- ============================================================
CREATE TABLE `admins` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)     NOT NULL,
  `email`      VARCHAR(100)    NOT NULL,
  `password`   VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
  `full_name`  VARCHAR(100)    NOT NULL,
  `avatar`     VARCHAR(255)    DEFAULT 'default_admin.png',
  `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
  `last_login` DATETIME        DEFAULT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email`    (`email`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data administrator';

-- ============================================================
-- 2. Tabel: users
-- ============================================================
CREATE TABLE `users` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)     NOT NULL,
  `email`         VARCHAR(100)    NOT NULL,
  `password`      VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
  `full_name`     VARCHAR(100)    NOT NULL,
  `avatar`        VARCHAR(255)    DEFAULT 'default_avatar.png',
  `bio`           TEXT            DEFAULT NULL,
  `date_of_birth` DATE            DEFAULT NULL,
  `gender`        ENUM('male','female','other') DEFAULT NULL,
  `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
  `is_verified`   TINYINT(1)      NOT NULL DEFAULT 0,
  `verify_token`  VARCHAR(64)     DEFAULT NULL,
  `reset_token`   VARCHAR(64)     DEFAULT NULL,
  `reset_expires` DATETIME        DEFAULT NULL,
  `last_login`    DATETIME        DEFAULT NULL,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email`    (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_is_active`      (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data pengguna';

-- ============================================================
-- 3. Tabel: artists
-- ============================================================
CREATE TABLE `artists` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150)    NOT NULL,
  `slug`        VARCHAR(160)    NOT NULL,
  `bio`         TEXT            DEFAULT NULL,
  `photo`       VARCHAR(255)    DEFAULT 'default_artist.png',
  `country`     VARCHAR(100)    DEFAULT NULL,
  `genre`       VARCHAR(100)    DEFAULT NULL,
  `monthly_listeners` INT UNSIGNED DEFAULT 0,
  `is_verified` TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_artists_slug`   (`slug`),
  KEY `idx_artists_genre`        (`genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data artis / musisi';

-- ============================================================
-- 4. Tabel: albums
-- ============================================================
CREATE TABLE `albums` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `artist_id`    INT UNSIGNED    NOT NULL,
  `title`        VARCHAR(200)    NOT NULL,
  `slug`         VARCHAR(210)    NOT NULL,
  `cover`        VARCHAR(255)    DEFAULT 'default_cover.png',
  `description`  TEXT            DEFAULT NULL,
  `release_date` DATE            DEFAULT NULL,
  `album_type`   ENUM('album','single','ep','compilation') NOT NULL DEFAULT 'album',
  `genre`        VARCHAR(100)    DEFAULT NULL,
  `total_tracks` INT UNSIGNED    NOT NULL DEFAULT 0,
  `is_published`  TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_albums_slug`     (`slug`),
  KEY `idx_albums_artist_id`      (`artist_id`),
  KEY `idx_albums_release_date`   (`release_date`),
  CONSTRAINT `fk_albums_artist`
    FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data album';

-- ============================================================
-- 5. Tabel: songs
-- ============================================================
CREATE TABLE `songs` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `artist_id`   INT UNSIGNED    NOT NULL,
  `album_id`    INT UNSIGNED    DEFAULT NULL,
  `title`       VARCHAR(200)    NOT NULL,
  `slug`        VARCHAR(210)    NOT NULL,
  `file_path`   VARCHAR(255)    NOT NULL COMMENT 'path file audio di server',
  `cover`       VARCHAR(255)    DEFAULT NULL COMMENT 'override cover dari album',
  `duration`    INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'durasi dalam detik',
  `track_number` SMALLINT UNSIGNED DEFAULT NULL,
  `genre`       VARCHAR(100)    DEFAULT NULL,
  `lyrics`      LONGTEXT        DEFAULT NULL,
  `play_count`  BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)     NOT NULL DEFAULT 1,
  `is_explicit`  TINYINT(1)     NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_songs_slug`       (`slug`),
  KEY `idx_songs_artist_id`        (`artist_id`),
  KEY `idx_songs_album_id`         (`album_id`),
  KEY `idx_songs_genre`            (`genre`),
  KEY `idx_songs_play_count`       (`play_count`),
  CONSTRAINT `fk_songs_artist`
    FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_songs_album`
    FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data lagu';

-- ============================================================
-- 6. Tabel: playlists
-- ============================================================
CREATE TABLE `playlists` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(200)    NOT NULL,
  `slug`        VARCHAR(210)    NOT NULL,
  `description` TEXT            DEFAULT NULL,
  `cover`       VARCHAR(255)    DEFAULT 'default_playlist.png',
  `is_public`   TINYINT(1)      NOT NULL DEFAULT 1,
  `total_songs` INT UNSIGNED    NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_playlists_slug`   (`slug`),
  KEY `idx_playlists_user_id`      (`user_id`),
  KEY `idx_playlists_is_public`    (`is_public`),
  CONSTRAINT `fk_playlists_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel data playlist milik user';

-- ============================================================
-- 7. Tabel: playlist_songs (pivot)
-- ============================================================
CREATE TABLE `playlist_songs` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `playlist_id` INT UNSIGNED    NOT NULL,
  `song_id`     INT UNSIGNED    NOT NULL,
  `position`    SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'urutan lagu dalam playlist',
  `added_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_playlist_song` (`playlist_id`, `song_id`),
  KEY `idx_ps_song_id`          (`song_id`),
  CONSTRAINT `fk_ps_playlist`
    FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_song`
    FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel pivot lagu di dalam playlist';

-- ============================================================
-- 8. Tabel: favorites
-- ============================================================
CREATE TABLE `favorites` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED    NOT NULL,
  `song_id`    INT UNSIGNED    NOT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_favorites_user_song` (`user_id`, `song_id`),
  KEY `idx_favorites_song_id`         (`song_id`),
  CONSTRAINT `fk_favorites_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_song`
    FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel lagu favorit user';

-- ============================================================
-- 9. Tabel: history
-- ============================================================
CREATE TABLE `history` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED    NOT NULL,
  `song_id`     INT UNSIGNED    NOT NULL,
  `played_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `duration_played` INT UNSIGNED DEFAULT 0 COMMENT 'berapa detik diputar',
  PRIMARY KEY (`id`),
  KEY `idx_history_user_id`   (`user_id`),
  KEY `idx_history_song_id`   (`song_id`),
  KEY `idx_history_played_at` (`played_at`),
  CONSTRAINT `fk_history_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_history_song`
    FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabel riwayat putar lagu user';

-- ============================================================
-- ============================================================
--  SAMPLE DATA
-- ============================================================
-- ============================================================

-- ============================================================
-- Admins (password: Admin@123  →  bcrypt)
-- ============================================================
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `is_active`) VALUES
('superadmin', 'admin@sultify.com',
 '$2y$12$eImiTXuWVxfM37uY4JANjQ==.TtGCh.M8L29D/dEJ6tDGcm0RQ8Vi',
 'Super Administrator', 1);

-- ============================================================
-- Users  (password: User@123  →  bcrypt)
-- ============================================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `is_active`, `is_verified`) VALUES
('sulton',   'sulton@mail.com',  '$2y$12$TKnzv3OXkx5mUg8.a3Gk1.mAP90vYeq2KH6ZIhkX7.sXF1Y6a6Bim', 'Sulton Raihan',    1, 1),
('aditya',   'aditya@mail.com',  '$2y$12$TKnzv3OXkx5mUg8.a3Gk1.mAP90vYeq2KH6ZIhkX7.sXF1Y6a6Bim', 'Aditya Pratama',   1, 1),
('budi',     'budi@mail.com',    '$2y$12$TKnzv3OXkx5mUg8.a3Gk1.mAP90vYeq2KH6ZIhkX7.sXF1Y6a6Bim', 'Budi Santoso',     1, 0),
('cindy',    'cindy@mail.com',   '$2y$12$TKnzv3OXkx5mUg8.a3Gk1.mAP90vYeq2KH6ZIhkX7.sXF1Y6a6Bim', 'Cindy Maharani',   1, 1),
('devano',   'devano@mail.com',  '$2y$12$TKnzv3OXkx5mUg8.a3Gk1.mAP90vYeq2KH6ZIhkX7.sXF1Y6a6Bim', 'Devano Kusuma',    1, 1);

-- ============================================================
-- Artists
-- ============================================================
INSERT INTO `artists` (`name`, `slug`, `bio`, `country`, `genre`, `monthly_listeners`, `is_verified`) VALUES
('Pamungkas',      'pamungkas',      'Singer-songwriter asal Jakarta yang dikenal dengan lagu romantis bernuansa indie pop.',              'Indonesia', 'Indie Pop',   3500000, 1),
('Raisa',          'raisa',          'Penyanyi pop Indonesia dengan suara khas dan karisma panggung yang luar biasa.',                      'Indonesia', 'Pop',         5200000, 1),
('Tulus',          'tulus',          'Musisi jazz-pop Indonesia dengan lirik bermakna dan aransemen yang memukau.',                         'Indonesia', 'Jazz Pop',    4100000, 1),
('Hindia',         'hindia',         'Proyek solo Baskara Putra yang dikenal membawa musik pop dengan sentimen introspektif.',              'Indonesia', 'Indie Pop',   2800000, 1),
('Weird Genius',   'weird-genius',   'Trio producer elektronik Indonesia yang berhasil menembus chart internasional.',                      'Indonesia', 'Electronic',  4700000, 1),
('Rich Brian',     'rich-brian',     'Rapper-producer Indonesia yang berbasis di AS, ikon musik hip-hop Asia.',                             'Indonesia', 'Hip-Hop',     6100000, 1),
('Yura Yunita',    'yura-yunita',    'Penyanyi pop dan soul Indonesia dengan vokal bertenaga tinggi.',                                      'Indonesia', 'Pop Soul',    2300000, 1),
('Fourtwnty',      'fourtwnty',      'Band folk indie asal Jakarta dengan lirik puitis dan melodik yang khas.',                             'Indonesia', 'Folk Indie',  1900000, 1);

-- ============================================================
-- Albums
-- ============================================================
INSERT INTO `albums` (`artist_id`, `title`, `slug`, `release_date`, `album_type`, `genre`, `total_tracks`, `is_published`) VALUES
(1, 'Solipsism',            'pamungkas-solipsism',         '2018-11-09', 'album',  'Indie Pop',  10, 1),
(1, 'Walk The Talk',        'pamungkas-walk-the-talk',     '2021-03-26', 'album',  'Indie Pop',  12, 1),
(2, 'Handmade',             'raisa-handmade',              '2019-08-22', 'album',  'Pop',        11, 1),
(3, 'Monokrom',             'tulus-monokrom',              '2016-04-20', 'album',  'Jazz Pop',   10, 1),
(4, 'Evaluasi',             'hindia-evaluasi',             '2019-09-27', 'album',  'Indie Pop',   9, 1),
(5, 'Lathi (Single)',       'weird-genius-lathi',          '2020-04-17', 'single', 'Electronic',  1, 1),
(6, 'Amen',                 'rich-brian-amen',             '2022-03-25', 'album',  'Hip-Hop',    12, 1),
(7, 'Melayang',             'yura-yunita-melayang',        '2021-06-04', 'album',  'Pop Soul',   10, 1),
(8, 'Lelaku',               'fourtwnty-lelaku',            '2017-06-22', 'album',  'Folk Indie', 10, 1);

-- ============================================================
-- Songs
-- ============================================================
INSERT INTO `songs` (`artist_id`, `album_id`, `title`, `slug`, `file_path`, `duration`, `track_number`, `genre`, `play_count`, `is_published`) VALUES
-- Pamungkas
(1, 1, 'To The Bone',       'pamungkas-to-the-bone',        'songs/pamungkas-to-the-bone.mp3',        212, 1, 'Indie Pop', 15400000, 1),
(1, 1, 'I Love You But I\'m Letting Go', 'pamungkas-i-love-you',    'songs/pamungkas-i-love-you.mp3',         198, 2, 'Indie Pop',  9800000, 1),
(1, 2, 'Walk The Talk',     'pamungkas-walk-the-talk-song', 'songs/pamungkas-walk-the-talk.mp3',      225, 1, 'Indie Pop',  7300000, 1),
(1, 2, 'Antara Kita',       'pamungkas-antara-kita',        'songs/pamungkas-antara-kita.mp3',        204, 3, 'Indie Pop',  5100000, 1),
-- Raisa
(2, 3, 'Kali Kedua',        'raisa-kali-kedua',             'songs/raisa-kali-kedua.mp3',             236, 1, 'Pop',       11200000, 1),
(2, 3, 'Terjebak Nostalgia','raisa-terjebak-nostalgia',     'songs/raisa-terjebak-nostalgia.mp3',     253, 2, 'Pop',        8900000, 1),
(2, 3, 'LDR',               'raisa-ldr',                    'songs/raisa-ldr.mp3',                    220, 5, 'Pop',        6700000, 1),
-- Tulus
(3, 4, 'Monokrom',          'tulus-monokrom-song',          'songs/tulus-monokrom.mp3',               243, 1, 'Jazz Pop',  13500000, 1),
(3, 4, 'Gajah',             'tulus-gajah',                  'songs/tulus-gajah.mp3',                  256, 3, 'Jazz Pop',   7600000, 1),
(3, 4, 'Sepatu',            'tulus-sepatu',                 'songs/tulus-sepatu.mp3',                 218, 5, 'Jazz Pop',   5400000, 1),
-- Hindia
(4, 5, 'Evaluasi',          'hindia-evaluasi-song',         'songs/hindia-evaluasi.mp3',              231, 1, 'Indie Pop',  9100000, 1),
(4, 5, 'Secukupnya',        'hindia-secukupnya',            'songs/hindia-secukupnya.mp3',            207, 3, 'Indie Pop',  6300000, 1),
-- Weird Genius
(5, 6, 'Lathi',             'weird-genius-lathi-song',      'songs/weird-genius-lathi.mp3',           195, 1, 'Electronic',18700000, 1),
-- Rich Brian
(6, 7, 'Holy',              'rich-brian-holy',              'songs/rich-brian-holy.mp3',              188, 2, 'Hip-Hop',   10200000, 1),
(6, 7, 'BALI',              'rich-brian-bali',              'songs/rich-brian-bali.mp3',              213, 5, 'Hip-Hop',    7800000, 1),
-- Yura Yunita
(7, 8, 'Melayang',          'yura-yunita-melayang-song',    'songs/yura-yunita-melayang.mp3',         246, 1, 'Pop Soul',   4900000, 1),
(7, 8, 'Cinta dan Rahasia', 'yura-yunita-cinta-dan-rahasia','songs/yura-yunita-cinta-dan-rahasia.mp3',228, 3, 'Pop Soul',   3700000, 1),
-- Fourtwnty
(8, 9, 'Zona Nyaman',       'fourtwnty-zona-nyaman',        'songs/fourtwnty-zona-nyaman.mp3',        238, 1, 'Folk Indie',16500000, 1),
(8, 9, 'Fana Merah Jambu',  'fourtwnty-fana-merah-jambu',   'songs/fourtwnty-fana-merah-jambu.mp3',   251, 4, 'Folk Indie', 8200000, 1),
(8, 9, 'Sementara Menutup Mata','fourtwnty-sementara',      'songs/fourtwnty-sementara.mp3',          265, 7, 'Folk Indie', 5600000, 1);

-- ============================================================
-- Playlists
-- ============================================================
INSERT INTO `playlists` (`user_id`, `name`, `slug`, `description`, `is_public`, `total_songs`) VALUES
(1, 'Santai Sore',       'sulton-santai-sore',       'Lagu-lagu enak buat santai di sore hari.',     1, 5),
(1, 'Workout Mix',       'sulton-workout-mix',       'Playlist semangat buat olahraga.',             1, 4),
(2, 'Sad Vibes',         'aditya-sad-vibes',         'Untuk saat-saat galau.',                       1, 4),
(3, 'Indie Hits',        'budi-indie-hits',          'Kumpulan indie terbaik Indonesia.',             0, 3),
(4, 'Morning Coffee',    'cindy-morning-coffee',     'Teman kopi pagi hari.',                        1, 4);

-- ============================================================
-- Playlist Songs
-- ============================================================
INSERT INTO `playlist_songs` (`playlist_id`, `song_id`, `position`) VALUES
-- Santai Sore (playlist 1)
(1, 1,  1), (1, 8,  2), (1, 5,  3), (1, 18, 4), (1, 11, 5),
-- Workout Mix (playlist 2)
(2, 13, 1), (2, 14, 2), (2, 15, 3), (2, 3,  4),
-- Sad Vibes (playlist 3)
(3, 2,  1), (3, 6,  2), (3, 10, 3), (3, 12, 4),
-- Indie Hits (playlist 4)
(4, 18, 1), (4, 19, 2), (4, 20, 3),
-- Morning Coffee (playlist 5)
(5, 8,  1), (5, 9,  2), (5, 11, 3), (5, 16, 4);

-- ============================================================
-- Favorites
-- ============================================================
INSERT INTO `favorites` (`user_id`, `song_id`) VALUES
(1, 1), (1, 8),  (1, 13), (1, 18), (1, 5),
(2, 2), (2, 6),  (2, 11), (2, 19),
(3, 13),(3, 14), (3, 18),
(4, 8), (4, 9),  (4, 10), (4, 16),
(5, 1), (5, 3),  (5, 7),  (5, 20);

-- ============================================================
-- History (riwayat putar)
-- ============================================================
INSERT INTO `history` (`user_id`, `song_id`, `played_at`, `duration_played`) VALUES
(1, 1,  '2026-06-16 08:10:00', 212),
(1, 8,  '2026-06-16 08:14:00', 243),
(1, 13, '2026-06-16 09:00:00', 195),
(1, 18, '2026-06-16 12:30:00', 238),
(1, 5,  '2026-06-16 18:45:00', 236),
(2, 2,  '2026-06-16 07:30:00', 198),
(2, 6,  '2026-06-16 07:35:00', 253),
(2, 11, '2026-06-16 19:20:00', 231),
(3, 13, '2026-06-16 10:00:00', 195),
(3, 14, '2026-06-16 10:05:00', 188),
(4, 9,  '2026-06-15 09:00:00', 256),
(4, 8,  '2026-06-15 09:05:00', 243),
(4, 16, '2026-06-15 20:00:00', 246),
(5, 20, '2026-06-16 11:00:00', 265),
(5, 19, '2026-06-16 11:05:00', 251),
(5, 1,  '2026-06-16 14:00:00', 212);

COMMIT;

-- ============================================================
-- VIEWS BERGUNA
-- ============================================================

-- View: top songs (berdasarkan play count)
CREATE OR REPLACE VIEW `v_top_songs` AS
SELECT
  s.id,
  s.title,
  s.slug,
  s.file_path,
  s.duration,
  s.play_count,
  s.cover,
  a.name  AS artist_name,
  a.slug  AS artist_slug,
  al.title AS album_title,
  al.cover AS album_cover
FROM songs s
JOIN artists a  ON s.artist_id = a.id
LEFT JOIN albums al ON s.album_id = al.id
WHERE s.is_published = 1
ORDER BY s.play_count DESC;

-- View: detail lagu dengan artis & album
CREATE OR REPLACE VIEW `v_song_detail` AS
SELECT
  s.id,
  s.title,
  s.slug,
  s.file_path,
  COALESCE(s.cover, al.cover, 'default_cover.png') AS cover,
  s.duration,
  s.track_number,
  s.genre,
  s.lyrics,
  s.play_count,
  s.is_explicit,
  a.id    AS artist_id,
  a.name  AS artist_name,
  a.slug  AS artist_slug,
  al.id   AS album_id,
  al.title AS album_title,
  al.slug  AS album_slug,
  al.release_date
FROM songs s
JOIN artists a   ON s.artist_id = a.id
LEFT JOIN albums al ON s.album_id = al.id
WHERE s.is_published = 1;

-- View: playlist dengan total durasi
CREATE OR REPLACE VIEW `v_playlist_detail` AS
SELECT
  p.id,
  p.name,
  p.slug,
  p.description,
  p.cover,
  p.is_public,
  p.total_songs,
  u.username,
  u.full_name AS owner_name,
  COALESCE(SUM(s.duration), 0) AS total_duration
FROM playlists p
JOIN users u ON p.user_id = u.id
LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
LEFT JOIN songs s ON ps.song_id = s.id
GROUP BY p.id;

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER $$

-- Procedure: tambah play count lagu
CREATE PROCEDURE `sp_increment_play_count`(IN p_song_id INT UNSIGNED)
BEGIN
  UPDATE songs SET play_count = play_count + 1 WHERE id = p_song_id;
END$$

-- Procedure: ambil lagu dalam playlist (terurut)
CREATE PROCEDURE `sp_get_playlist_songs`(IN p_playlist_id INT UNSIGNED)
BEGIN
  SELECT
    ps.position,
    s.id         AS song_id,
    s.title,
    s.file_path,
    COALESCE(s.cover, al.cover, 'default_cover.png') AS cover,
    s.duration,
    a.name       AS artist_name
  FROM playlist_songs ps
  JOIN songs s   ON ps.song_id   = s.id
  JOIN artists a ON s.artist_id  = a.id
  LEFT JOIN albums al ON s.album_id = al.id
  WHERE ps.playlist_id = p_playlist_id
  ORDER BY ps.position ASC;
END$$

-- Procedure: riwayat putar user (10 terakhir)
CREATE PROCEDURE `sp_get_user_history`(IN p_user_id INT UNSIGNED)
BEGIN
  SELECT
    h.played_at,
    h.duration_played,
    s.id         AS song_id,
    s.title,
    COALESCE(s.cover, al.cover, 'default_cover.png') AS cover,
    a.name       AS artist_name
  FROM history h
  JOIN songs s   ON h.song_id    = s.id
  JOIN artists a ON s.artist_id  = a.id
  LEFT JOIN albums al ON s.album_id = al.id
  WHERE h.user_id = p_user_id
  ORDER BY h.played_at DESC
  LIMIT 50;
END$$

DELIMITER ;

-- ============================================================
-- Selesai! Database Sultify siap digunakan.
-- ============================================================
