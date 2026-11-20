<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120132301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__artwork AS SELECT id, title, date, image_id, artist_id FROM artwork');
        $this->addSql('DROP TABLE artwork');
        $this->addSql('CREATE TABLE artwork (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date VARCHAR(30) DEFAULT NULL, image_id VARCHAR(255) NOT NULL, artist_id INTEGER NOT NULL, aic_id INTEGER DEFAULT NULL, CONSTRAINT FK_881FC576B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO artwork (id, title, date, image_id, artist_id) SELECT id, title, date, image_id, artist_id FROM __temp__artwork');
        $this->addSql('DROP TABLE __temp__artwork');
        $this->addSql('CREATE INDEX IDX_881FC576B7970CF8 ON artwork (artist_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_881FC57656DB2CAF ON artwork (aic_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__artwork AS SELECT id, title, date, image_id, artist_id FROM artwork');
        $this->addSql('DROP TABLE artwork');
        $this->addSql('CREATE TABLE artwork (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date VARCHAR(30) DEFAULT NULL, image_id VARCHAR(255) NOT NULL, artist_id INTEGER NOT NULL, CONSTRAINT FK_881FC576B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO artwork (id, title, date, image_id, artist_id) SELECT id, title, date, image_id, artist_id FROM __temp__artwork');
        $this->addSql('DROP TABLE __temp__artwork');
        $this->addSql('CREATE INDEX IDX_881FC576B7970CF8 ON artwork (artist_id)');
    }
}
