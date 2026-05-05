CREATE DATABASE IF NOT EXISTS forum_web_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON forum_web_testing.* TO 'laravel'@'%';
FLUSH PRIVILEGES;
