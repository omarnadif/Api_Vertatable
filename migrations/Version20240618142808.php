<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240618142808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id INT AUTO_INCREMENT NOT NULL, numero INT DEFAULT NULL, rue VARCHAR(50) DEFAULT NULL, code_postal INT DEFAULT NULL, ville VARCHAR(50) DEFAULT NULL, pays VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE allergene (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(20) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE allergene_utilisateur (allergene_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_A0D6B4CE4646AB2 (allergene_id), INDEX IDX_A0D6B4CEFB88E14F (utilisateur_id), PRIMARY KEY(allergene_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, date_commande DATE NOT NULL, etat VARCHAR(20) NOT NULL, quantite INT NOT NULL, note VARCHAR(50) NOT NULL, date_avis DATE NOT NULL, commentaire VARCHAR(255) NOT NULL, INDEX IDX_6EEAA67DFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande_plat (commande_id INT NOT NULL, plat_id INT NOT NULL, INDEX IDX_4B54A3E482EA2E54 (commande_id), INDEX IDX_4B54A3E4D73DB560 (plat_id), PRIMARY KEY(commande_id, plat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, adresse_id INT NOT NULL, nom VARCHAR(20) NOT NULL, telephone VARCHAR(13) NOT NULL, code_entreprise VARCHAR(5) NOT NULL, UNIQUE INDEX UNIQ_D19FA604DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(20) NOT NULL, description VARCHAR(255) NOT NULL, date_disponibilite DATE NOT NULL, image VARCHAR(255) DEFAULT NULL, ingredients JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_categorie (plat_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_B5FAB76ED73DB560 (plat_id), INDEX IDX_B5FAB76EBCF5E72D (categorie_id), PRIMARY KEY(plat_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plat_allergene (plat_id INT NOT NULL, allergene_id INT NOT NULL, INDEX IDX_6FA44BBFD73DB560 (plat_id), INDEX IDX_6FA44BBF4646AB2 (allergene_id), PRIMARY KEY(plat_id, allergene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(20) NOT NULL, date_de_naissance DATE NOT NULL, telephone VARCHAR(13) NOT NULL, INDEX IDX_1D1C63B3A4AEAFEA (entreprise_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE allergene_utilisateur ADD CONSTRAINT FK_A0D6B4CE4646AB2 FOREIGN KEY (allergene_id) REFERENCES allergene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE allergene_utilisateur ADD CONSTRAINT FK_A0D6B4CEFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commande_plat ADD CONSTRAINT FK_4B54A3E482EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_plat ADD CONSTRAINT FK_4B54A3E4D73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA604DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE plat_categorie ADD CONSTRAINT FK_B5FAB76ED73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_categorie ADD CONSTRAINT FK_B5FAB76EBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_allergene ADD CONSTRAINT FK_6FA44BBFD73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_allergene ADD CONSTRAINT FK_6FA44BBF4646AB2 FOREIGN KEY (allergene_id) REFERENCES allergene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergene_utilisateur DROP FOREIGN KEY FK_A0D6B4CE4646AB2');
        $this->addSql('ALTER TABLE allergene_utilisateur DROP FOREIGN KEY FK_A0D6B4CEFB88E14F');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
        $this->addSql('ALTER TABLE commande_plat DROP FOREIGN KEY FK_4B54A3E482EA2E54');
        $this->addSql('ALTER TABLE commande_plat DROP FOREIGN KEY FK_4B54A3E4D73DB560');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA604DE7DC5C');
        $this->addSql('ALTER TABLE plat_categorie DROP FOREIGN KEY FK_B5FAB76ED73DB560');
        $this->addSql('ALTER TABLE plat_categorie DROP FOREIGN KEY FK_B5FAB76EBCF5E72D');
        $this->addSql('ALTER TABLE plat_allergene DROP FOREIGN KEY FK_6FA44BBFD73DB560');
        $this->addSql('ALTER TABLE plat_allergene DROP FOREIGN KEY FK_6FA44BBF4646AB2');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3A4AEAFEA');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE allergene');
        $this->addSql('DROP TABLE allergene_utilisateur');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_plat');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE plat');
        $this->addSql('DROP TABLE plat_categorie');
        $this->addSql('DROP TABLE plat_allergene');
        $this->addSql('DROP TABLE utilisateur');
    }
}
