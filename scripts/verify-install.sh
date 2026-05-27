#!/usr/bin/env bash
# QuickPrints BMS — verify a fresh install is configured correctly.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

errors=0

check() {
    if "$@"; then
        echo "  OK   $*"
    else
        echo "  FAIL $*"
        errors=$((errors + 1))
    fi
}

echo "==> QuickPrints install check"
echo ""

[[ -f .env ]] && echo "  OK   .env exists" || { echo "  FAIL .env missing (copy from .env.example)"; errors=$((errors + 1)); }

if [[ -f .env ]] && grep -q '^DB_CONNECTION=mysql' .env; then
    echo "  OK   DB_CONNECTION=mysql"
elif [[ -f .env ]] && grep -q '^DB_CONNECTION=sqlite' .env; then
    echo "  FAIL DB_CONNECTION=sqlite — switch to mysql in .env"
    errors=$((errors + 1))
else
    echo "  WARN DB_CONNECTION not set to mysql in .env"
    errors=$((errors + 1))
fi

[[ -n "$(grep '^APP_KEY=base64:' .env 2>/dev/null || true)" ]] && echo "  OK   APP_KEY is set" || { echo "  FAIL APP_KEY missing — run: php artisan key:generate"; errors=$((errors + 1)); }

php artisan db:show 2>/dev/null | grep -q 'mysql' && echo "  OK   Laravel connected to MySQL" || { echo "  FAIL Laravel not using MySQL — run: php artisan config:clear && php artisan db:show"; errors=$((errors + 1)); }

[[ -d vendor ]] && echo "  OK   vendor/ exists" || { echo "  FAIL run: composer install"; errors=$((errors + 1)); }

[[ -L public/storage || -d public/storage ]] && echo "  OK   storage link exists" || { echo "  WARN run: php artisan storage:link"; }

if ! grep -q 'RewriteBase /quickprints' public/.htaccess 2>/dev/null; then
    echo "  OK   public/.htaccess has no hardcoded XAMPP RewriteBase"
else
    echo "  FAIL public/.htaccess contains RewriteBase /quickprints — remove for production"
    errors=$((errors + 1))
fi

echo ""
if [[ $errors -eq 0 ]]; then
    echo "All checks passed."
    exit 0
else
    echo "$errors check(s) failed. See README.md → Installation / Deployment."
    exit 1
fi
