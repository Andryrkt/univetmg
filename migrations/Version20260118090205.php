<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118090205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ligne_vente_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vente_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, adresse TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN client.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE ligne_vente (id INT NOT NULL, vente_id INT NOT NULL, produit_id INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, sous_total NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B26C07C7DC7170A ON ligne_vente (vente_id)');
        $this->addSql('CREATE INDEX IDX_8B26C07CF347EFB ON ligne_vente (produit_id)');
        $this->addSql('CREATE TABLE vente (id INT NOT NULL, client_id INT DEFAULT NULL, user_id INT NOT NULL, numero_facture VARCHAR(255) NOT NULL, date_vente TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total NUMERIC(10, 2) NOT NULL, statut VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_888A2A4C38D27AB1 ON vente (numero_facture)');
        $this->addSql('CREATE INDEX IDX_888A2A4C19EB6921 ON vente (client_id)');
        $this->addSql('CREATE INDEX IDX_888A2A4CA76ED395 ON vente (user_id)');
        $this->addSql('COMMENT ON COLUMN vente.date_vente IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ligne_vente ADD CONSTRAINT FK_8B26C07C7DC7170A FOREIGN KEY (vente_id) REFERENCES vente (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ligne_vente ADD CONSTRAINT FK_8B26C07CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4C19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ligne_vente_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE vente_id_seq CASCADE');
        $this->addSql('ALTER TABLE ligne_vente DROP CONSTRAINT FK_8B26C07C7DC7170A');
        $this->addSql('ALTER TABLE ligne_vente DROP CONSTRAINT FK_8B26C07CF347EFB');
        $this->addSql('ALTER TABLE vente DROP CONSTRAINT FK_888A2A4C19EB6921');
        $this->addSql('ALTER TABLE vente DROP CONSTRAINT FK_888A2A4CA76ED395');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE ligne_vente');
        $this->addSql('DROP TABLE vente');
    }
}
