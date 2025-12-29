#!/bin/bash
set -e

BRANCH="main"
TARGET="/home/univetmg/univet"
REPO="/home/univetmg/repo.git"

log() {
    echo "[DEPLOY] $1"
}

while read oldrev newrev ref
do
    if [[ "$ref" = "refs/heads/$BRANCH" ]]; then

        log "🚀 Déploiement branche $BRANCH..."

        log "📥 Mise à jour du code..."
        git --work-tree="$TARGET" --git-dir="$REPO" checkout -f "$BRANCH"

        cd "$TARGET" || exit 1

        # Forcer l'environnement AVANT tout
        export APP_ENV=prod
        export APP_DEBUG=0

        # Installer composer.phar si pas encore présent
        if [ ! -f "$TARGET/composer.phar" ]; then
            log "📦 Installation Composer..."
            cd /tmp
            php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
            php composer-setup.php --install-dir="$TARGET" --filename=composer.phar
            rm -f composer-setup.php
            cd "$TARGET" || exit 1
        fi

        # Installer les dépendances
        log "📚 Installation des dépendances (production)..."
        php "$TARGET/composer.phar" install \
            --no-interaction \
            --no-dev \
            --optimize-autoloader \
            --no-scripts \
            --working-dir="$TARGET"

        # Dump autoload optimisé
        log "🔄 Optimisation autoload..."
        php "$TARGET/composer.phar" dump-autoload --optimize --no-dev --working-dir="$TARGET"

        # Clear cache de manière sécurisée
        log "🧹 Clear cache..."
        
        if [ -d "$TARGET/var/cache/prod" ]; then
            rm -rf "$TARGET/var/cache/prod"
        fi
        
        php "$TARGET/bin/console" cache:warmup --env=prod --no-debug 2>/dev/null || log "⚠️  Cache warmup ignoré"

        # Permissions
        log "🔐 Permissions..."
        chmod -R 775 "$TARGET/var" 2>/dev/null || true
        chown -R univetmg:univetmg "$TARGET" 2>/dev/null || true

        log "✅ Déploiement terminé !"
    fi
done
fsdfsq