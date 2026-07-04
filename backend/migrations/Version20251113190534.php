<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113190534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE unite_conversion_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE conditionnement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conditionnement (id INT NOT NULL, produit_id INT NOT NULL, unite_id INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F4BEA3AF347EFB ON conditionnement (produit_id)');
        $this->addSql('CREATE INDEX IDX_3F4BEA3AEC4A74AB ON conditionnement (unite_id)');
        $this->addSql('ALTER TABLE conditionnement ADD CONSTRAINT FK_3F4BEA3AF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conditionnement ADD CONSTRAINT FK_3F4BEA3AEC4A74AB FOREIGN KEY (unite_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT fk_bba3637f11cd3824');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT fk_bba3637fe60bc51a');
        $this->addSql('ALTER TABLE unite_conversion DROP CONSTRAINT fk_bba3637ff347efb');
        $this->addSql('DROP TABLE unite_conversion');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE conditionnement_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE unite_conversion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE unite_conversion (id INT NOT NULL, produit_id INT DEFAULT NULL, unite_source_id INT NOT NULL, unite_cible_id INT DEFAULT NULL, facteur DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_bba3637f11cd3824 ON unite_conversion (unite_source_id)');
        $this->addSql('CREATE INDEX idx_bba3637fe60bc51a ON unite_conversion (unite_cible_id)');
        $this->addSql('CREATE INDEX idx_bba3637ff347efb ON unite_conversion (produit_id)');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT fk_bba3637f11cd3824 FOREIGN KEY (unite_source_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT fk_bba3637fe60bc51a FOREIGN KEY (unite_cible_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unite_conversion ADD CONSTRAINT fk_bba3637ff347efb FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conditionnement DROP CONSTRAINT FK_3F4BEA3AF347EFB');
        $this->addSql('ALTER TABLE conditionnement DROP CONSTRAINT FK_3F4BEA3AEC4A74AB');
        $this->addSql('DROP TABLE conditionnement');
    }
}
