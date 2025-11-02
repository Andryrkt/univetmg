# les installations fait par composer
composer require symfony/orm-pack #doctrine
composer require symfony/security-bundle 
composer require symfony/validator
composer require symfony/form
composer require symfony/maker-bundle --dev


# ligne de commande pour la base de donnée
php bin/console doctrine:database:create #pour la creation de la base de donnée


# verification du chemin php ini
php --ini

# verification des extension installer
php -m

# verification de version php
php -v

# connection postgres base de donnée
psql -U postgres -d univetmg_db
