<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration script for the new model with multi collectivity.
 * this script changes the table Classeur and ads the collectivity_id field
 * then it updates the new field with the collectivity id from the User tables through the foreign key.
 * The script then creates the new table Ref_Collectivite_User in order to create the manyToMany relation
 * between User and Collectivite
 * 
 */
class Version20180501141159 extends AbstractMigration
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

        $this->addSql('CREATE TABLE Ref_Collectivite_User (user_id INT NOT NULL, collectivite_id INT NOT NULL, INDEX IDX_762C809A76ED395 (user_id), INDEX IDX_762C809A7991F51 (collectivite_id), PRIMARY KEY(user_id, collectivite_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Ref_Collectivite_User ADD CONSTRAINT FK_762C809A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Ref_Collectivite_User ADD CONSTRAINT FK_762C809A7991F51 FOREIGN KEY (collectivite_id) REFERENCES Collectivite (id)');

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

        $this->addSql('DROP TABLE Ref_Collectivite_User');
    }
}
