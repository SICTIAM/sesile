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
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
