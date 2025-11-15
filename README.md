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

        # -------------------------------------------------------
        # Installer composer.phar si pas encore présent
        # -------------------------------------------------------
        if [ ! -f "$TARGET/composer.phar" ]; then
            log "📦 Installation Composer..."
            cd /tmp
            php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
            php composer-setup.php --install-dir="$TARGET" --filename=composer.phar
            rm -f composer-setup.php
            cd "$TARGET" || exit 1
        fi

        # -------------------------------------------------------
        # Installer les dépendances SANS les dev dependencies
        # -------------------------------------------------------
        log "📚 Installation des dépendances (production)..."
        php "$TARGET/composer.phar" install --no-interaction --no-dev --optimize-autoloader --working-dir="$TARGET"

        # -------------------------------------------------------
        # Clear cache Symfony en forçant l'environnement prod
        # -------------------------------------------------------
        log "🧹 Clear cache..."
        # Forcer l'environnement de production
        export APP_ENV=prod
        export APP_DEBUG=0
        php "$TARGET/bin/console" cache:clear --env=prod --no-debug

        # -------------------------------------------------------
        # Permissions
        # -------------------------------------------------------
        log "🔐 Permissions..."
        chmod -R 775 "$TARGET/var"
        chown -R univetmg:univetmg "$TARGET"

        log "✅ Déploiement terminé !"
    fi
done