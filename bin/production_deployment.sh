#!/bin/bash
set -e

# ─────────────────────────────────────────────────────────────────────────────
# NOT WORKING! git clone has to be done manually, as the Plesk server makes troubles!
# also installing node.js and making the build on the server is not working!
# build has to be done locally - check ASSETS_BUILD_README.MD
# abolute path on the Plesk server is: /home/httpd/vhosts/exedra.ch/httpdocs/pfad/zur/datei.php
# or: ~/httpdocs/pfad/zur/datei.php

DOMAIN="exedra.ch"

# 1) Force the environment to “prod”
export APP_ENV=prod
export APP_DEBUG=0

# ─────────────────────────────────────────────────────────────────────────────
# 1) Load NVM so that `node` / `npm` are available (if you’re building frontend)
#    (Skip this block if you chose to build assets locally and commit them.)
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
# Now `nvm use default` will pick Node 16 (which we installed earlier via nvm):
nvm use default

# ─────────────────────────────────────────────────────────────────────────────
# 2) Go into the /httpdocs application root folder
cd ..

# ─────────────────────────────────────────────────────────────────────────────
# 3) Ensure .env.prod is in place
if [ -f ".env.prod" ]; then
  echo "✅ .env.prod file found"
else
  echo "❌ .env.prod file not found → aborting"
  echo "   Please manually copy .env.prod to the server before deployment"
  exit 1
fi

# ─────────────────────────────────────────────────────────────────────────────
# 4) Install PHP (Composer) dependencies
if [ -f "composer.json" ]; then
  echo "➡️  Installing Composer dependencies…"
  /opt/php82/bin/php /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "❌  composer.json not found → aborting"
  exit 1
fi

# ─────────────────────────────────────────────────────────────────────────────
# 5) Check if pre-built assets exist, otherwise build frontend assets via Node 16
if [ -d "public/assets" ]; then
  echo "✅ Pre-built assets found in public/assets → skipping frontend build"
else
  if [ -f "package.json" ]; then
    echo "⚠️ No pre-built assets found in public/assets"
    echo "➡️ Running npm ci and npm run build…"
    npm ci --no-fund --no-audit
    npm run build
  else
    echo "ℹ️ No package.json → skipping frontend build"
  fi
fi

# ─────────────────────────────────────────────────────────────────────────────
# 6) Ensure var/cache and var/log exist, with correct ownership & perms
echo "➡️  Preparing var/cache and var/log…"
mkdir -p var/cache var/log
chown -R exedrac:psaserv var
chmod -R 775 var

# ─────────────────────────────────────────────────────────────────────────────
# 7) Run Doctrine migrations
echo "➡️  Running database migrations…"
/opt/php82/bin/php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# ─────────────────────────────────────────────────────────────────────────────
# 8) Clear & warmup Symfony cache
echo "➡️  Clearing cache…"
/opt/php82/bin/php bin/console cache:clear --env=prod --no-warmup
echo "➡️  Warming up cache…"
/opt/php82/bin/php bin/console cache:warmup --env=prod

# ─────────────────────────────────────────────────────────────────────────────
# 9) Ensure public/media is NOT deleted and has correct ownership & perms
echo "➡️  Ensuring public/media exists and is writable…"
# If public/media directory was never created by Git, make it now:
mkdir -p public/media

# chown and chmod everything under public/media:
chown -R exedrac:psaserv public/media
chmod -R 775 public/media

# ─────────────────────────────────────────────────────────────────────────────
# 10) Fix ownership on vendor/ (so that opcache & PHP‐FPM can read)
echo "➡️  Setting final ownership on vendor/…"
chown -R exedrac:psaserv vendor
chmod -R 775 vendor

# ─────────────────────────────────────────────────────────────────────────────
# 11) (Optional) Restart PHP-FPM to flush OPcache.
echo "➡️ Resetting OPcache..."
curl -X GET http://$DOMAIN/opcache-reset.php || echo "⚠️ OPcache reset failed, may need manual PHP-FPM restart"

echo "✅  Deployment completed successfully!"
