<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124105437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE lot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE lot (id INT NOT NULL, produit_id INT NOT NULL, numero_lot VARCHAR(255) DEFAULT NULL, quantite DOUBLE PRECISION NOT NULL, date_peremption DATE DEFAULT NULL, prix_achat DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B81291BF347EFB ON lot (produit_id)');
        $this->addSql('COMMENT ON COLUMN lot.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lot.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE lot ADD CONSTRAINT FK_B81291BF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE produit DROP stock_initial');
        $this->addSql('ALTER TABLE produit DROP prix_achat');
        $this->addSql('ALTER TABLE produit DROP date_peremption');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE lot_id_seq CASCADE');
        $this->addSql('ALTER TABLE lot DROP CONSTRAINT FK_B81291BF347EFB');
        $this->addSql('DROP TABLE lot');
        $this->addSql('ALTER TABLE produit ADD stock_initial DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE produit ADD prix_achat DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD date_peremption TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}
