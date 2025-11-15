#!/bin/bash

USER="univetmg"
PROJECT_DIR="/home/$USER/univet"
REPO_DIR="/home/$USER/repo.git"
BRANCH="main"
LOG_FILE="/home/$USER/deploy.log"

PHP="/usr/local/bin/php"
COMPOSER="/home/$USER/composer.phar"
ENV="prod"

log() {
    echo "[DEPLOY] $1"
    echo "[DEPLOY] $1" >> "$LOG_FILE"
}

while read oldrev newrev ref
do
    if [ "$ref" = "refs/heads/$BRANCH" ]; then

        log "🚀 Déploiement de la branche $BRANCH..."

        log "📥 Mise à jour du dossier de production..."
        git --work-tree="$PROJECT_DIR" --git-dir="$REPO_DIR" checkout -f "$BRANCH"

        if [ ! -f "$COMPOSER" ]; then
            log "📦 Installation de Composer..."
            curl -sS https://getcomposer.org/installer | "$PHP" -- --install-dir="/home/$USER" --filename="composer.phar"
        fi

        log "📚 Installation des dépendances Composer..."
        "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --working-dir="$PROJECT_DIR"

        if [ -f "$PROJECT_DIR/package.json" ]; then
            log "🎨 Build des assets..."
            cd "$PROJECT_DIR"
            npm install --silent
            npm run build
        fi

        log "🔐 Permissions..."
        chown -R "$USER":"$USER" "$PROJECT_DIR"
        find "$PROJECT_DIR/var" -type d -exec chmod 775 {} \;
        find "$PROJECT_DIR/var" -type f -exec chmod 664 {} \;

        log "🧹 Nettoyage du cache Symfony..."
        "$PHP" "$PROJECT_DIR/bin/console" cache:clear --env="$ENV"

        log "✅ Déploiement terminé avec succès !"
    fi
done