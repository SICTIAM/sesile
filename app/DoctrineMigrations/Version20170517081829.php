<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170517081829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Classeur_copy (classeur_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D16DD300EC10E96A (classeur_id), INDEX IDX_D16DD300A76ED395 (user_id), PRIMARY KEY(classeur_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Classeur_copy ADD CONSTRAINT FK_D16DD300EC10E96A FOREIGN KEY (classeur_id) REFERENCES Classeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Classeur_copy ADD CONSTRAINT FK_D16DD300A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Classeur_copy');
    }
}
