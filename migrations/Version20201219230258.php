<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201219230258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, first_card_id VARCHAR(10) NOT NULL, second_card_id VARCHAR(10) DEFAULT NULL, operation ENUM(\'W\', \'R\', \'T\') NOT NULL COMMENT \'(DC2Type:OperationType)\', value NUMERIC(12, 2) NOT NULL, INDEX IDX_723705D15940F6DC (first_card_id), INDEX IDX_723705D15D8E1A4D (second_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D15940F6DC FOREIGN KEY (first_card_id) REFERENCES card (number)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D15D8E1A4D FOREIGN KEY (second_card_id) REFERENCES card (number)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE transaction');
    }
}
