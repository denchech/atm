<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222125721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card ADD balance_usd NUMERIC(12, 2) NOT NULL, CHANGE balance balance_rub NUMERIC(12, 2) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD currency ENUM(\'R\', \'D\') NOT NULL COMMENT \'(DC2Type:CurrencyType)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card ADD balance NUMERIC(12, 2) NOT NULL, DROP balance_rub, DROP balance_usd');
        $this->addSql('ALTER TABLE transaction DROP currency');
    }
}
