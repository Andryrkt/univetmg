<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124090645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE promotion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE promotion (id INT NOT NULL, nom VARCHAR(255) NOT NULL, date_debut TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_fin TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, taux_remise NUMERIC(5, 2) DEFAULT NULL, montant_remise NUMERIC(10, 2) DEFAULT NULL, actif BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN promotion.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN promotion.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE promotion_produit (promotion_id INT NOT NULL, produit_id INT NOT NULL, PRIMARY KEY(promotion_id, produit_id))');
        $this->addSql('CREATE INDEX IDX_71D81A1D139DF194 ON promotion_produit (promotion_id)');
        $this->addSql('CREATE INDEX IDX_71D81A1DF347EFB ON promotion_produit (produit_id)');
        $this->addSql('CREATE TABLE type_client (id INT NOT NULL, nom VARCHAR(100) NOT NULL, taux_remise NUMERIC(5, 2) NOT NULL, description TEXT DEFAULT NULL, actif BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN type_client.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN type_client.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE promotion_produit ADD CONSTRAINT FK_71D81A1D139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE promotion_produit ADD CONSTRAINT FK_71D81A1DF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD type_client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455AD2D2831 FOREIGN KEY (type_client_id) REFERENCES type_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C7440455AD2D2831 ON client (type_client_id)');
        $this->addSql('ALTER TABLE conditionnement ADD prix_vente DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_vente ADD prix_catalogue NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_vente ADD taux_remise DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_vente ADD montant_remise NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_vente ADD type_remise VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455AD2D2831');
        $this->addSql('DROP SEQUENCE promotion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_client_id_seq CASCADE');
        $this->addSql('ALTER TABLE promotion_produit DROP CONSTRAINT FK_71D81A1D139DF194');
        $this->addSql('ALTER TABLE promotion_produit DROP CONSTRAINT FK_71D81A1DF347EFB');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE promotion_produit');
        $this->addSql('DROP TABLE type_client');
        $this->addSql('ALTER TABLE ligne_vente DROP prix_catalogue');
        $this->addSql('ALTER TABLE ligne_vente DROP taux_remise');
        $this->addSql('ALTER TABLE ligne_vente DROP montant_remise');
        $this->addSql('ALTER TABLE ligne_vente DROP type_remise');
        $this->addSql('ALTER TABLE conditionnement DROP prix_vente');
        $this->addSql('DROP INDEX IDX_C7440455AD2D2831');
        $this->addSql('ALTER TABLE client DROP type_client_id');
    }
}
