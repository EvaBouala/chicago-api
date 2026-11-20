<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120124623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE artwork (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date VARCHAR(30) DEFAULT NULL, image_id VARCHAR(255) NOT NULL, artist_id INTEGER NOT NULL, CONSTRAINT FK_881FC576B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_881FC576B7970CF8 ON artwork (artist_id)');
        $this->addSql('CREATE TABLE artwork_style (artwork_id INTEGER NOT NULL, style_id INTEGER NOT NULL, PRIMARY KEY (artwork_id, style_id), CONSTRAINT FK_A257D070DB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artwork (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A257D070BACD6074 FOREIGN KEY (style_id) REFERENCES style (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A257D070DB8FFA4 ON artwork_style (artwork_id)');
        $this->addSql('CREATE INDEX IDX_A257D070BACD6074 ON artwork_style (style_id)');
        $this->addSql('CREATE TABLE artwork_category (artwork_id INTEGER NOT NULL, category_id INTEGER NOT NULL, PRIMARY KEY (artwork_id, category_id), CONSTRAINT FK_FA06D53FDB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artwork (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FA06D53F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FA06D53FDB8FFA4 ON artwork_category (artwork_id)');
        $this->addSql('CREATE INDEX IDX_FA06D53F12469DE2 ON artwork_category (category_id)');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE TABLE style (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE artwork');
        $this->addSql('DROP TABLE artwork_style');
        $this->addSql('DROP TABLE artwork_category');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE style');
    }
}
