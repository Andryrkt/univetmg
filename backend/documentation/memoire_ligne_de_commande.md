# ligne de commande pour la base de donnée
php bin/console doctrine:database:create #pour la creation de la base de donnée
php bin/console make:migration
php bin/console doctrine:migrations:migrate


# Pour vider la base et recharger les fixtures
php bin/console doctrine:fixtures:load

# Ou pour ajouter sans vider la base
php bin/console doctrine:fixtures:load --append

# verification du chemin php ini
php --ini

# verification des extension installer
php -m

# verification de version php
php -v

# connection postgres base de donnée
psql -U postgres -d univetmg_db