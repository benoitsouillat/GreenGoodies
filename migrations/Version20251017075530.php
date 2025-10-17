<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017075530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD short_description LONGTEXT NOT NULL, ADD long_description LONGTEXT NOT NULL, DROP short_desc, DROP long_desc, CHANGE unit_price price DOUBLE PRECISION NOT NULL, CHANGE image picture VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD short_desc LONGTEXT NOT NULL, ADD long_desc LONGTEXT NOT NULL, DROP short_description, DROP long_description, CHANGE price unit_price DOUBLE PRECISION NOT NULL, CHANGE picture image VARCHAR(255) NOT NULL');
    }
}
