<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table mouvement_stock
 */
final class Version20251201180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table mouvement_stock pour la gestion des mouvements de stock';
    }

    public function up(Schema $schema): void
    {
        // Création de la table mouvement_stock
        $this->addSql('CREATE TABLE mouvement_stock (
            id SERIAL PRIMARY KEY,
            produit_id INT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            quantite DOUBLE PRECISION NOT NULL,
            date_creation TIMESTAMP NOT NULL,
            motif VARCHAR(255) DEFAULT NULL,
            reference VARCHAR(100) DEFAULT NULL,
            stock_avant DOUBLE PRECISION NOT NULL,
            stock_apres DOUBLE PRECISION NOT NULL,
            CONSTRAINT fk_mouvement_stock_produit FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE RESTRICT,
            CONSTRAINT fk_mouvement_stock_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
        )');

        // Index pour améliorer les performances
        $this->addSql('CREATE INDEX idx_mouvement_stock_produit ON mouvement_stock (produit_id)');
        $this->addSql('CREATE INDEX idx_mouvement_stock_user ON mouvement_stock (user_id)');
        $this->addSql('CREATE INDEX idx_mouvement_stock_type ON mouvement_stock (type)');
        $this->addSql('CREATE INDEX idx_mouvement_stock_date ON mouvement_stock (date_creation)');
    }

    public function down(Schema $schema): void
    {
        // Suppression de la table mouvement_stock
        $this->addSql('DROP TABLE IF EXISTS mouvement_stock');
    }
}
