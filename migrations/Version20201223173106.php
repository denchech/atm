<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223173106 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE card MODIFY COLUMN type ENUM('D','C','P','E')");
        $this->addSql('CREATE TABLE external_card (number VARCHAR(10) NOT NULL, pin VARCHAR(255) NOT NULL, balance NUMERIC(12, 2) NOT NULL, PRIMARY KEY(number)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE card MODIFY COLUMN type ENUM('D','C','P')");
        $this->addSql('DROP TABLE external_card');
    }
}
