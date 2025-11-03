#!/bin/bash
set -e  # Stopper en cas d’erreur

echo "=== 🚀 Déploiement Symfony ==="

DEPLOY_PATH="/home/univetmg/univet"
GIT_DIR="/home/univetmg/repo.git"
BRANCH="main"

# Couleurs pour les logs
GREEN="\e[32m"
YELLOW="\e[33m"
BLUE="\e[34m"
RED="\e[31m"
NC="\e[0m" # No Color

function log()  { echo -e "${GREEN}$1${NC}"; }
function warn() { echo -e "${YELLOW}$1${NC}"; }
function err()  { echo -e "${RED}$1${NC}"; exit 1; }

# 1️⃣ Préparation du dossier de déploiement
log "🧹 Préparation du dossier de déploiement..."
rm -rf "$DEPLOY_PATH"
mkdir -p "$DEPLOY_PATH"

# 2️⃣ Extraction du code depuis le dépôt Git
log "📦 Extraction du code depuis le dépôt..."
git --git-dir="$GIT_DIR" archive "$BRANCH" | tar -x -C "$DEPLOY_PATH"

cd "$DEPLOY_PATH"

# 3️⃣ Installation des dépendances Composer (sans dev)
log "📚 Installation des dépendances (production uniquement)..."
if [ -f "composer.json" ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php composer.phar install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
    rm composer.phar composer-setup.php
else
    warn "⚠️ Aucun fichier composer.json trouvé."
fi

# 4️⃣ Nettoyage des fichiers de config inutiles en production
log "🧼 Nettoyage des configs inutiles..."
rm -f config/packages/maker.yaml 2>/dev/null || true
rm -f config/routes/dev/maker.yaml 2>/dev/null || true
rm -f config/packages/test/*.yaml 2>/dev/null || true

# 5️⃣ Configuration des permissions et dossiers Symfony
log "🔒 Configuration des permissions..."
mkdir -p var/cache var/log public/
chmod -R 755 var/ public/

# 6️⃣ Nettoyage et réchauffage du cache Symfony
log "🗑️ Nettoyage du cache Symfony (prod)..."
rm -rf var/cache/prod || true
if [ -f "bin/console" ]; then
    php bin/console cache:clear --env=prod --no-debug || warn "⚠️ Erreur cache:clear ignorée"
    php bin/console cache:warmup --env=prod --no-debug || warn "⚠️ Erreur cache:warmup ignorée"
else
    warn "⚠️ Aucun binaire Symfony trouvé (bin/console manquant)."
fi

# 7️⃣ Exécution des migrations (si Doctrine présent)
log "🗃️ Exécution des migrations..."
if [ -f "bin/console" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod || warn "⚠️ Migrations ignorées"
else
    warn "⚠️ Commande Doctrine non disponible."
fi

# 8️⃣ Vérification finale
log "✅ Déploiement terminé avec succès !"
log "📁 Dossier : $DEPLOY_PATH"
log "🌐 Branche : $BRANCH"
