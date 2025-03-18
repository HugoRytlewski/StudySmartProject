<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318162139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annotation (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, user_id INT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2E443EF2C33F7837 (document_id), INDEX IDX_2E443EF2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annotation DROP FOREIGN KEY FK_2E443EF2C33F7837');
        $this->addSql('ALTER TABLE annotation DROP FOREIGN KEY FK_2E443EF2A76ED395');
        $this->addSql('DROP TABLE annotation');
    }
}
