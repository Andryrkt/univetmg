<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113193230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE conversion_standard_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conversion_standard (id INT NOT NULL, unite_origine_id INT NOT NULL, unite_cible_id INT NOT NULL, facteur DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A111D6806B5DDB5 ON conversion_standard (unite_origine_id)');
        $this->addSql('CREATE INDEX IDX_A111D680E60BC51A ON conversion_standard (unite_cible_id)');
        $this->addSql('ALTER TABLE conversion_standard ADD CONSTRAINT FK_A111D6806B5DDB5 FOREIGN KEY (unite_origine_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversion_standard ADD CONSTRAINT FK_A111D680E60BC51A FOREIGN KEY (unite_cible_id) REFERENCES unite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE conversion_standard_id_seq CASCADE');
        $this->addSql('ALTER TABLE conversion_standard DROP CONSTRAINT FK_A111D6806B5DDB5');
        $this->addSql('ALTER TABLE conversion_standard DROP CONSTRAINT FK_A111D680E60BC51A');
        $this->addSql('DROP TABLE conversion_standard');
    }
}
