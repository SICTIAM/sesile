<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration script for the new model with multi collectivity.
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

        $this->addSql('ALTER TABLE Collectivite ADD siren VARCHAR(10) DEFAULT NULL, ADD textcopymailnew VARCHAR(3000) DEFAULT NULL, ADD textcopymailwalid VARCHAR(3000) DEFAULT NULL, CHANGE pageSignature pageSignature INT DEFAULT 0');
        $this->addSql('ALTER TABLE EtapeClasseur ADD etapeValide TINYINT(1) DEFAULT \'0\' NOT NULL, ADD date DATETIME DEFAULT NULL, ADD userValidant INT DEFAULT NULL, CHANGE EtapeValidante EtapeValidante TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE EtapeClasseur ADD CONSTRAINT FK_B476E85F4DBC755C FOREIGN KEY (userValidant) REFERENCES User (id)');
        $this->addSql('CREATE INDEX IDX_B476E85F4DBC755C ON EtapeClasseur (userValidant)');
        $this->addSql('ALTER TABLE Groupe DROP couleur, DROP json, DROP ordreEtape');
        $this->addSql('ALTER TABLE User ADD ozwilloId VARCHAR(255) DEFAULT NULL, CHANGE sesile_version sesile_version DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE TypeClasseur ADD collectivites INT DEFAULT NULL');
        $this->addSql('ALTER TABLE TypeClasseur ADD CONSTRAINT FK_6C71B47CBA10F87D FOREIGN KEY (collectivites) REFERENCES Collectivite (id)');
        $this->addSql('CREATE INDEX IDX_6C71B47CBA10F87D ON TypeClasseur (collectivites)');
        $this->addSql('ALTER TABLE Classeur ADD circuit_id INT DEFAULT NULL, ADD collectivite_id INT DEFAULT NULL, ADD motifRefus VARCHAR(255) DEFAULT NULL, DROP EtapeDeposante, CHANGE user user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Classeur ADD CONSTRAINT FK_2829E10C8D93D649 FOREIGN KEY (user) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Classeur ADD CONSTRAINT FK_2829E10CCF2182C8 FOREIGN KEY (circuit_id) REFERENCES Groupe (id)');
        $this->addSql('ALTER TABLE Classeur ADD CONSTRAINT FK_2829E10CA7991F51 FOREIGN KEY (collectivite_id) REFERENCES Collectivite (id)');
        $this->addSql('CREATE INDEX IDX_2829E10C8D93D649 ON Classeur (user)');
        $this->addSql('CREATE INDEX IDX_2829E10CCF2182C8 ON Classeur (circuit_id)');
        $this->addSql('CREATE INDEX IDX_2829E10CA7991F51 ON Classeur (collectivite_id)');
        $this->addSql('UPDATE Classeur c SET c.collectivite_id = (SELECT u.collectivite FROM  User u WHERE u.id=c.user) WHERE c.collectivite_id IS NULL;');
        $this->addSql('CREATE TABLE Ref_Collectivite_User (user_id INT NOT NULL, collectivite_id INT NOT NULL, INDEX IDX_762C809A76ED395 (user_id), INDEX IDX_762C809A7991F51 (collectivite_id), PRIMARY KEY(user_id, collectivite_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT INTO Ref_Collectivite_User (user_id, collectivite_id) (SELECT u.id as user_id, u.collectivite as collectivite_id FROM User u where u.collectivite is NOT NULL)');
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

        $this->addSql('ALTER TABLE Classeur DROP FOREIGN KEY FK_2829E10C8D93D649');
        $this->addSql('ALTER TABLE Classeur DROP FOREIGN KEY FK_2829E10CCF2182C8');
        $this->addSql('ALTER TABLE Classeur DROP FOREIGN KEY FK_2829E10CA7991F51');
        $this->addSql('DROP INDEX IDX_2829E10C8D93D649 ON Classeur');
        $this->addSql('DROP INDEX IDX_2829E10CCF2182C8 ON Classeur');
        $this->addSql('DROP INDEX IDX_2829E10CA7991F51 ON Classeur');
        $this->addSql('ALTER TABLE Classeur ADD EtapeDeposante INT NOT NULL, DROP circuit_id, DROP collectivite_id, DROP motifRefus, CHANGE user user INT NOT NULL');
        $this->addSql('ALTER TABLE Collectivite DROP siren, DROP textcopymailnew, DROP textcopymailwalid, CHANGE pageSignature pageSignature INT DEFAULT NULL');
        $this->addSql('ALTER TABLE EtapeClasseur DROP FOREIGN KEY FK_B476E85F4DBC755C');
        $this->addSql('DROP INDEX IDX_B476E85F4DBC755C ON EtapeClasseur');
        $this->addSql('ALTER TABLE EtapeClasseur DROP etapeValide, DROP date, DROP userValidant, CHANGE EtapeValidante EtapeValidante INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Groupe ADD couleur VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD json LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD ordreEtape VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE TypeClasseur DROP FOREIGN KEY FK_6C71B47CBA10F87D');
        $this->addSql('DROP INDEX IDX_6C71B47CBA10F87D ON TypeClasseur');
        $this->addSql('ALTER TABLE TypeClasseur DROP collectivites');
        $this->addSql('ALTER TABLE User DROP ozwilloId, CHANGE sesile_version sesile_version DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('DROP TABLE Ref_Collectivite_User');


    }
}
