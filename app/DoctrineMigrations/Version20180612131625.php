<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration script for the two new properties of Collectivite "textcopymailnew" and "textcopymailwalid"(valid)
 * This script add default messages
 */
class Version20180612131625 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql(
            'UPDATE Collectivite c 
                SET c.textcopymailnew = "<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d\'être déposé par {{ deposant }}, pour validation à {{ validant }}, à la date du <strong>{{ date_limite | date(\'d/m/Y\') }}.</strong></p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>"
                WHERE c.textcopymailnew IS NULL;');

        $this->addSql(
            'UPDATE Collectivite c 
                SET c.textcopymailwalid = "<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d\'être validé par {{ validant }}.</p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>"
                WHERE c.textcopymailwalid IS NULL;');
        $this->addSql('CREATE TABLE sesile_migration (id INT AUTO_INCREMENT NOT NULL, collectivity_id VARCHAR(255) NOT NULL, siren VARCHAR(9) NOT NULL, status VARCHAR(10) NOT NULL, users_exported TINYINT(1) NOT NULL, old_id VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP TABLE sesile_migration');
    }
}
