<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225131051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_user (conversation_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5AECB5559AC0396 (conversation_id), INDEX IDX_5AECB555A76ED395 (user_id), PRIMARY KEY(conversation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favoris (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, annonces_id INT DEFAULT NULL, INDEX IDX_8933C432A76ED395 (user_id), INDEX IDX_8933C4324C2885D7 (annonces_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, conversation_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6BD307FF675F31B (author_id), INDEX IDX_B6BD307F9AC0396 (conversation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre (id INT AUTO_INCREMENT NOT NULL, annonce_name_id INT NOT NULL, annonce_owner_id INT NOT NULL, offer_maker_id INT NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_AF86866F8B7806B6 (annonce_name_id), INDEX IDX_AF86866F33746F3B (annonce_owner_id), INDEX IDX_AF86866F11A7EEA1 (offer_maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, souk_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, discout_price DOUBLE PRECISION NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_D34A04AD7E3C61F9 (owner_id), INDEX IDX_D34A04ADFD5BABD8 (souk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, objet VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, INDEX IDX_CE606404A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE souk (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_souke DATE NOT NULL, end_souke DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE souk_user (souk_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_EC1B08F3FD5BABD8 (souk_id), INDEX IDX_EC1B08F3A76ED395 (user_id), PRIMARY KEY(souk_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_5AECB5559AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT FK_5AECB555A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C4324C2885D7 FOREIGN KEY (annonces_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F8B7806B6 FOREIGN KEY (annonce_name_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F33746F3B FOREIGN KEY (annonce_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F11A7EEA1 FOREIGN KEY (offer_maker_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADFD5BABD8 FOREIGN KEY (souk_id) REFERENCES souk (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE souk_user ADD CONSTRAINT FK_EC1B08F3FD5BABD8 FOREIGN KEY (souk_id) REFERENCES souk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE souk_user ADD CONSTRAINT FK_EC1B08F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_5AECB5559AC0396');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY FK_5AECB555A76ED395');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432A76ED395');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C4324C2885D7');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F8B7806B6');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F33746F3B');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F11A7EEA1');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7E3C61F9');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADFD5BABD8');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395');
        $this->addSql('ALTER TABLE souk_user DROP FOREIGN KEY FK_EC1B08F3FD5BABD8');
        $this->addSql('ALTER TABLE souk_user DROP FOREIGN KEY FK_EC1B08F3A76ED395');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_user');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE souk');
        $this->addSql('DROP TABLE souk_user');
    }
}
