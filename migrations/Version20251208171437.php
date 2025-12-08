<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208171437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('SELECT setval(\'user_id_seq\', (SELECT MAX(id) FROM users))');
        $this->addSql('DROP INDEX idx_mouvement_stock_date');
        $this->addSql('DROP INDEX idx_mouvement_stock_type');
        $this->addSql('ALTER TABLE mouvement_stock ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE mouvement_stock ALTER type TYPE VARCHAR(255)');
        $this->addSql('ALTER INDEX idx_mouvement_stock_produit RENAME TO IDX_61E2C8EBF347EFB');
        $this->addSql('ALTER INDEX idx_mouvement_stock_user RENAME TO IDX_61E2C8EBA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mouvement_stock_id_seq');
        $this->addSql('SELECT setval(\'mouvement_stock_id_seq\', (SELECT MAX(id) FROM mouvement_stock))');
        $this->addSql('ALTER TABLE mouvement_stock ALTER id SET DEFAULT nextval(\'mouvement_stock_id_seq\')');
        $this->addSql('ALTER TABLE mouvement_stock ALTER type TYPE VARCHAR(20)');
        $this->addSql('CREATE INDEX idx_mouvement_stock_date ON mouvement_stock (date_creation)');
        $this->addSql('CREATE INDEX idx_mouvement_stock_type ON mouvement_stock (type)');
        $this->addSql('ALTER INDEX idx_61e2c8ebf347efb RENAME TO idx_mouvement_stock_produit');
        $this->addSql('ALTER INDEX idx_61e2c8eba76ed395 RENAME TO idx_mouvement_stock_user');
    }
}
