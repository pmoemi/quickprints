#!/usr/bin/env bash
# QuickPrints BMS — production deploy helper
# Run from the project root after git pull and .env is configured.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

echo "==> QuickPrints deploy"

if [[ ! -f .env ]]; then
    echo "ERROR: .env not found. Copy .env.example and configure MySQL first."
    exit 1
fi

if grep -q '^DB_CONNECTION=sqlite' .env 2>/dev/null; then
    echo "ERROR: .env uses SQLite. Set DB_CONNECTION=mysql before deploying."
    exit 1
fi

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan migrate --force
php artisan storage:link 2>/dev/null || true

echo ""
echo "==> Verify database connection:"
php artisan db:show

echo ""
echo "==> Done."
echo "    1. Document root must point to: ${ROOT}/public"
echo "    2. Web server must own storage & bootstrap/cache:"
echo "       sudo chown -R www:www storage bootstrap/cache"
echo "       sudo chmod -R 775 storage bootstrap/cache"
echo "    3. First deploy only: php artisan db:seed --force"
echo "    4. Then optionally: php artisan config:cache && php artisan route:cache"
