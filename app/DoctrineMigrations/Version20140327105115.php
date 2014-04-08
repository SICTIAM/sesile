<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140327105115 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE User ADD collectivite INT DEFAULT NULL");
        $this->addSql("ALTER TABLE User ADD CONSTRAINT FK_2DA17977CFA408A1 FOREIGN KEY (collectivite) REFERENCES Collectivite (id)");
        $this->addSql("CREATE INDEX IDX_2DA17977CFA408A1 ON User (collectivite)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE User DROP FOREIGN KEY FK_2DA17977CFA408A1");
        $this->addSql("DROP INDEX IDX_2DA17977CFA408A1 ON User");
        $this->addSql("ALTER TABLE User DROP collectivite");
    }
}
