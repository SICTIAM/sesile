<?php


namespace Sesile\MigrationBundle\Migrator;

use Sesile\MainBundle\Domain\Message;

/**
 * Interface SesileMigratorInterface
 * @package Sesile\MigrationBundle\Migrator
 */
interface SesileMigratorInterface
{
    /**
     * @param $identifier
     * @return Message
     */
    public function migrate($identifier);

}