<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionV4CollectiviteClasseur extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Classeur ADD collectivite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Classeur ADD CONSTRAINT FK_2829E10CA7991F51 FOREIGN KEY (collectivite_id) REFERENCES Collectivite (id)');
        $this->addSql('CREATE INDEX IDX_2829E10CA7991F51 ON Classeur (collectivite_id)');
        $this->addSql('UPDATE Classeur c SET c.collectivite_id = (SELECT u.collectivite FROM  User u WHERE u.id=c.user) WHERE c.collectivite_id IS NULL;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Classeur DROP FOREIGN KEY FK_2829E10CA7991F51');
        $this->addSql('DROP INDEX IDX_2829E10CA7991F51 ON Classeur');
        $this->addSql('ALTER TABLE Classeur DROP collectivite_id');
    }
}
