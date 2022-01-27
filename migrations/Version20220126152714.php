<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220126152714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE storage (id INT AUTO_INCREMENT NOT NULL, storage_author_id INT DEFAULT NULL, storage_name VARCHAR(255) DEFAULT NULL, storage_size VARCHAR(255) DEFAULT NULL, storage_real_name VARCHAR(255) DEFAULT NULL, INDEX IDX_547A1B341E6EEC14 (storage_author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE storage ADD CONSTRAINT FK_547A1B341E6EEC14 FOREIGN KEY (storage_author_id) REFERENCES `User` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE storage');
    }
}
