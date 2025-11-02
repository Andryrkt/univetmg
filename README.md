# les installations fait par composer
composer require symfony/orm-pack #doctrine
composer require symfony/security-bundle 
composer require symfony/validator
composer require symfony/form
composer require symfony/maker-bundle --dev
composer require symfony/twig-bundle
composer require symfonycasts/verify-email-bundle symfony/mailer
composer require --dev phpunit/phpunit symfony/test-pack

#pour la vue
composer require pentatrion/vite-bundle

# les installation fait par npm
npm install vite vite-plugin-symfony
npm install bootstrap @popperjs/core
npm install -D sass-embedded

univetmg/
├─ assets/
│  ├─ app.js
│  └─ styles/
│     └─ app.scss
├─ public/
│  ├─ build/        ← se crée après “npm run build”
│  └─ index.php
├─ templates/
│  └─ base.html.twig
├─ vite.config.js
├─ package.json
└─ composer.json

# ligne de commande pour la base de donnée
php bin/console doctrine:database:create #pour la creation de la base de donnée
php bin/console make:migration
php bin/console doctrine:migrations:migrate


# verification du chemin php ini
php --ini

# verification des extension installer
php -m

# verification de version php
php -v

# connection postgres base de donnée
psql -U postgres -d univetmg_db
