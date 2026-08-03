#!/bin/sh
set -e

LOG_FILE=/var/www/telegram_bot/storage/logs/php_errors.log

mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"
chown www-data:www-data "$LOG_FILE"

tail -n 0 -F "$LOG_FILE" &

exec php-fpm
