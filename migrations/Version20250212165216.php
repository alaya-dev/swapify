<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212165216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, livreur_id INT DEFAULT NULL, id_expediteur_id INT DEFAULT NULL, id_distinataire_id INT DEFAULT NULL, statut VARCHAR(255) NOT NULL, date DATE NOT NULL, localisation_expediteur_lat DOUBLE PRECISION DEFAULT NULL, localisation_expediteur_lng DOUBLE PRECISION DEFAULT NULL, localisation_destinataire_lat DOUBLE PRECISION DEFAULT NULL, localisation_destinataire_lng DOUBLE PRECISION DEFAULT NULL, payment_exp VARCHAR(255) DEFAULT NULL, payment_dist VARCHAR(255) DEFAULT NULL, telephone_expediteur INT NOT NULL, code_postal_expediteur INT NOT NULL, telephone_destinataire INT DEFAULT NULL, code_postal_destinataire INT DEFAULT NULL, INDEX IDX_A60C9F1FF8646701 (livreur_id), INDEX IDX_A60C9F1FAE1B8E04 (id_expediteur_id), INDEX IDX_A60C9F1F67531DCB (id_distinataire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livreur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, societe VARCHAR(255) NOT NULL, contact VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FF8646701 FOREIGN KEY (livreur_id) REFERENCES livreur (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FAE1B8E04 FOREIGN KEY (id_expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F67531DCB FOREIGN KEY (id_distinataire_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FF8646701');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FAE1B8E04');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F67531DCB');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE livreur');
    }
}
