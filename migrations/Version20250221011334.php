<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221011334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance (id INT AUTO_INCREMENT NOT NULL, session_id INT DEFAULT NULL, participant_event_id INT DEFAULT NULL, attended TINYINT(1) NOT NULL, timestamp DATETIME DEFAULT NULL, code INT NOT NULL, INDEX IDX_6DE30D91613FECDF (session_id), INDEX IDX_6DE30D917FB66D76 (participant_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, contenu VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, rate INT DEFAULT 0 NOT NULL, rate_count INT DEFAULT 0 NOT NULL, statut VARCHAR(255) DEFAULT \'enAttente\' NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C0155143A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_user_ratings (blog_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CC67F644DAE07E97 (blog_id), INDEX IDX_CC67F644A76ED395 (user_id), PRIMARY KEY(blog_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, date_acquisition DATE NOT NULL, valid TINYINT(1) NOT NULL, INDEX IDX_219CDA4AA76ED395 (user_id), INDEX IDX_219CDA4A71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, blog_id INT DEFAULT NULL, user_id INT NOT NULL, contenu_cmt VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_67F068BCDAE07E97 (blog_id), INDEX IDX_67F068BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, orgniser_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, max_participant INT NOT NULL, status VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_3BAE0AA73D082202 (orgniser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant_event (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, inscription_date DATETIME NOT NULL, INDEX IDX_FA1BA31EA76ED395 (user_id), INDEX IDX_FA1BA31E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, start_hour DATETIME NOT NULL, end_hour DATETIME NOT NULL, type_session VARCHAR(255) NOT NULL, objective VARCHAR(255) DEFAULT NULL, meeting_started TINYINT(1) NOT NULL, INDEX IDX_D044D5D471F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D917FB66D76 FOREIGN KEY (participant_event_id) REFERENCES participant_event (id)');
        $this->addSql('ALTER TABLE blog ADD CONSTRAINT FK_C0155143A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE blog_user_ratings ADD CONSTRAINT FK_CC67F644DAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_user_ratings ADD CONSTRAINT FK_CC67F644A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCDAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73D082202 FOREIGN KEY (orgniser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant_event ADD CONSTRAINT FK_FA1BA31EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant_event ADD CONSTRAINT FK_FA1BA31E71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91613FECDF');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D917FB66D76');
        $this->addSql('ALTER TABLE blog DROP FOREIGN KEY FK_C0155143A76ED395');
        $this->addSql('ALTER TABLE blog_user_ratings DROP FOREIGN KEY FK_CC67F644DAE07E97');
        $this->addSql('ALTER TABLE blog_user_ratings DROP FOREIGN KEY FK_CC67F644A76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A71F7E88B');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCDAE07E97');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73D082202');
        $this->addSql('ALTER TABLE participant_event DROP FOREIGN KEY FK_FA1BA31EA76ED395');
        $this->addSql('ALTER TABLE participant_event DROP FOREIGN KEY FK_FA1BA31E71F7E88B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D471F7E88B');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP TABLE blog');
        $this->addSql('DROP TABLE blog_user_ratings');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE participant_event');
        $this->addSql('DROP TABLE session');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
