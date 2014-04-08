<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140408093650 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE UserHierarchie DROP FOREIGN KEY FK_5BC9CB3D3D8E604F");
        $this->addSql("CREATE TABLE UserGroupe (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, groupe INT DEFAULT NULL, parent INT NOT NULL, INDEX IDX_24B29D038D93D649 (user), INDEX IDX_24B29D034B98C21 (groupe), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE UserGroupe ADD CONSTRAINT FK_24B29D038D93D649 FOREIGN KEY (user) REFERENCES User (id)");
        $this->addSql("ALTER TABLE UserGroupe ADD CONSTRAINT FK_24B29D034B98C21 FOREIGN KEY (groupe) REFERENCES Groupe (id) ON DELETE CASCADE");
        $this->addSql("DROP TABLE UserHierarchie");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE UserHierarchie (id INT AUTO_INCREMENT NOT NULL, parent INT DEFAULT NULL, groupe INT DEFAULT NULL, UserId VARCHAR(255) NOT NULL, INDEX IDX_5BC9CB3D3D8E604F (parent), INDEX IDX_5BC9CB3D4B98C21 (groupe), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE UserHierarchie ADD CONSTRAINT FK_5BC9CB3D3D8E604F FOREIGN KEY (parent) REFERENCES UserHierarchie (id)");
        $this->addSql("ALTER TABLE UserHierarchie ADD CONSTRAINT FK_5BC9CB3D4B98C21 FOREIGN KEY (groupe) REFERENCES Groupe (id) ON DELETE CASCADE");
        $this->addSql("DROP TABLE UserGroupe");
    }
}
