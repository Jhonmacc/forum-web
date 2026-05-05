#!/usr/bin/env bash

set -euo pipefail

export APP_ENV="${APP_ENV:-testing}"
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-mysql}"
export DB_PORT="${DB_PORT:-3306}"
export DB_DATABASE="${DB_DATABASE:-forum_web_testing}"
export DB_USERNAME="${DB_USERNAME:-laravel}"
export DB_PASSWORD="${DB_PASSWORD:-secret}"
export CACHE_STORE="${CACHE_STORE:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export SESSION_DRIVER="${SESSION_DRIVER:-array}"
export MAIL_MAILER="${MAIL_MAILER:-array}"

blocked_databases=("forum_web" "laravel" "mysql" "performance_schema" "information_schema" "sys")

for blocked_database in "${blocked_databases[@]}"; do
    if [[ "${DB_DATABASE}" == "${blocked_database}" ]]; then
        echo "Refusing to run tests against unsafe database: ${DB_DATABASE}" >&2
        echo "Use an isolated testing database such as forum_web_testing." >&2
        exit 1
    fi
done

if [[ "${APP_ENV}" != "testing" ]]; then
    echo "Refusing to run tests outside APP_ENV=testing. Current APP_ENV=${APP_ENV}" >&2
    exit 1
fi

if [[ -z "${DB_DATABASE}" || "${DB_DATABASE}" != *"test"* ]]; then
    echo "Refusing to run tests because DB_DATABASE must clearly be a test database." >&2
    echo "Current DB_DATABASE=${DB_DATABASE:-<empty>}" >&2
    exit 1
fi

php artisan test "$@"
