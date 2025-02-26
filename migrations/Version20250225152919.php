<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225152919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance (id INT AUTO_INCREMENT NOT NULL, session_id INT DEFAULT NULL, participant_event_id INT DEFAULT NULL, attended TINYINT(1) NOT NULL, timestamp DATETIME DEFAULT NULL, code INT NOT NULL, INDEX IDX_6DE30D91613FECDF (session_id), INDEX IDX_6DE30D917FB66D76 (participant_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_user (conversation_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5AECB5559AC0396 (conversation_id), INDEX IDX_5AECB555A76ED395 (user_id), PRIMARY KEY(conversation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, orgniser_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, max_participant INT NOT NULL, status VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_3BAE0AA73D082202 (orgniser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, livreur_id INT DEFAULT NULL, id_expediteur_id INT DEFAULT NULL, id_distinataire_id INT DEFAULT NULL, statut VARCHAR(255) NOT NULL, date DATE NOT NULL, localisation_expediteur_lat DOUBLE PRECISION DEFAULT NULL, localisation_expediteur_lng DOUBLE PRECISION DEFAULT NULL, localisation_destinataire_lat DOUBLE PRECISION DEFAULT NULL, localisation_destinataire_lng DOUBLE PRECISION DEFAULT NULL, payment_exp VARCHAR(255) DEFAULT NULL, payment_dist VARCHAR(255) DEFAULT NULL, telephone_expediteur INT NOT NULL, code_postal_expediteur INT NOT NULL, telephone_destinataire INT DEFAULT NULL, code_postal_destinataire INT DEFAULT NULL, adresse_expediteur VARCHAR(255) NOT NULL, adresse_destiniataire VARCHAR(255) DEFAULT NULL, INDEX IDX_A60C9F1FF8646701 (livreur_id), INDEX IDX_A60C9F1FAE1B8E04 (id_expediteur_id), INDEX IDX_A60C9F1F67531DCB (id_distinataire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livreur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, societe VARCHAR(255) NOT NULL, contact VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_event (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, inscription_date DATETIME NOT NULL, INDEX IDX_FA1BA31EA76ED395 (user_id), INDEX IDX_FA1BA31E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, id_recepteur_id INT DEFAULT NULL, id_donneur_id INT DEFAULT NULL, rating INT NOT NULL, INDEX IDX_D889262218880D5F (id_recepteur_id), INDEX IDX_D88926223203028C (id_donneur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, start_hour DATETIME NOT NULL, end_hour DATETIME NOT NULL, type_session VARCHAR(255) NOT NULL, objective VARCHAR(255) DEFAULT NULL, meeting_started TINYINT(1) NOT NULL, INDEX IDX_D044D5D471F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D917FB66D76 FOREIGN KEY (participant_event_id) REFERENCES participant_event (id)');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_5AECB5559AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_5AECB555A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73D082202 FOREIGN KEY (orgniser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FF8646701 FOREIGN KEY (livreur_id) REFERENCES livreur (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FAE1B8E04 FOREIGN KEY (id_expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F67531DCB FOREIGN KEY (id_distinataire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant_event ADD CONSTRAINT FK_FA1BA31EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant_event ADD CONSTRAINT FK_FA1BA31E71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262218880D5F FOREIGN KEY (id_recepteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926223203028C FOREIGN KEY (id_donneur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A71F7E88B');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91613FECDF');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D917FB66D76');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_5AECB5559AC0396');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_5AECB555A76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73D082202');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FF8646701');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FAE1B8E04');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F67531DCB');
        $this->addSql('ALTER TABLE participant_event DROP FOREIGN KEY FK_FA1BA31EA76ED395');
        $this->addSql('ALTER TABLE participant_event DROP FOREIGN KEY FK_FA1BA31E71F7E88B');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D889262218880D5F');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D88926223203028C');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D471F7E88B');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP TABLE conversation_user');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE livreur');
        $this->addSql('DROP TABLE participant_event');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE session');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
    }
}
