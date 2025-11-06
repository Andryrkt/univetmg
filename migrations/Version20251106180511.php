<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106180511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE categorie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE fournisseur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE produit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE unite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE unite_conversion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE categorie (id INT NOT NULL, parent_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_497DD634727ACA70 ON categorie (parent_id)');
        $this->addSql('CREATE TABLE fournisseur (id INT NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(50) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, email VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE produit (id INT NOT NULL, unite_de_base_id INT NOT NULL, categorie_id INT DEFAULT NULL, fournisseur_id INT DEFAULT NULL, nom VARCHAR(150) NOT NULL, description VARCHAR(255) DEFAULT NULL, code VARCHAR(50) NOT NULL, stock_initial DOUBLE PRECISION NOT NULL, stock_minimum DOUBLE PRECISION NOT NULL, prix_achat DOUBLE PRECISION DEFAULT NULL, prix_vente DOUBLE PRECISION DEFAULT NULL, date_peremption TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_29A5EC27534DADF9 ON produit (unite_de_base_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27670C757F ON produit (fournisseur_id)');
        $this->addSql('CREATE TABLE unite (id INT NOT NULL, nom VARCHAR(50) NOT NULL, symbole VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE unite_conversion (id INT NOT NULL, produit_id INT DEFAULT NULL, unite_source_id INT NOT NULL, unite_cible_id INT DEFAULT NULL, facteur DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BBA3637FF347EFB ON unite_conversion (produit_id)');
        $this->addSql('CREATE INDEX IDX_BBA3637F11CD3824 ON unite_conversion (unite_source_id)');
        $this->addSql('CREATE INDEX IDX_BBA3637FE60BC51A ON unite_conversion (unite_cible_id)');
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD634727ACA70 FOREIGN KEY (parent_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27534DADF9 FOREIGN KEY (unite_de_base_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT FK_BBA3637FF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT FK_BBA3637F11CD3824 FOREIGN KEY (unite_source_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT FK_BBA3637FE60BC51A FOREIGN KEY (unite_cible_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE categorie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE fournisseur_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE produit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE unite_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE unite_conversion_id_seq CASCADE');
        $this->addSql('ALTER TABLE categorie DROP CONSTRAINT FK_497DD634727ACA70');
        $this->addSql('ALTER TABLE produit DROP CONSTRAINT FK_29A5EC27534DADF9');
        $this->addSql('ALTER TABLE produit DROP CONSTRAINT FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE produit DROP CONSTRAINT FK_29A5EC27670C757F');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT FK_BBA3637FF347EFB');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT FK_BBA3637F11CD3824');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT FK_BBA3637FE60BC51A');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE unite');
        $this->addSql('DROP TABLE unite_conversion');
    }
}
