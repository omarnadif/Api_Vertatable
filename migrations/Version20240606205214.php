<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606205214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse CHANGE rue rue VARCHAR(50) DEFAULT NULL, CHANGE ville ville VARCHAR(50) DEFAULT NULL, CHANGE pays pays VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE plat CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE ingredients ingredients JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse CHANGE rue rue VARCHAR(50) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(50) DEFAULT \'NULL\', CHANGE pays pays VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE plat CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE ingredients ingredients LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
