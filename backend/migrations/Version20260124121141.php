<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124121141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM mouvement_stock');
        $this->addSql('ALTER TABLE categorie ADD abbreviation VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE mouvement_stock DROP CONSTRAINT fk_mouvement_stock_produit');
        $this->addSql('DROP INDEX idx_61e2c8ebf347efb');
        $this->addSql('ALTER TABLE mouvement_stock RENAME COLUMN produit_id TO lot_id');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBA8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_61E2C8EBA8CBA5F7 ON mouvement_stock (lot_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE mouvement_stock DROP CONSTRAINT FK_61E2C8EBA8CBA5F7');
        $this->addSql('DROP INDEX IDX_61E2C8EBA8CBA5F7');
        $this->addSql('ALTER TABLE mouvement_stock RENAME COLUMN lot_id TO produit_id');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT fk_mouvement_stock_produit FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_61e2c8ebf347efb ON mouvement_stock (produit_id)');
        $this->addSql('ALTER TABLE categorie DROP abbreviation');
    }
}
