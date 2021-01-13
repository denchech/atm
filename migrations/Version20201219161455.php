<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201219161455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE card (number VARCHAR(10) NOT NULL, pin VARCHAR(4) NOT NULL, balance NUMERIC(12, 2) NOT NULL, type ENUM(\'D\', \'C\', \'P\') NOT NULL COMMENT \'(DC2Type:CardType)\', PRIMARY KEY(number)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE card');
    }
}
