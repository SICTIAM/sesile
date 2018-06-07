<?php


namespace Sesile\MigrationBundle\Service;

/**
 * Class LegacyCollectivityService
 * @package Sesile\MigrationBundle\Service
 */
class LegacyCollectivityService
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * LegacyCollectivityService constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getCollectivityList()
    {
        $query = "SELECT id, nom as name, domain FROM Collectivite";
        return $this->connection->query($query)->fetchAll(\PDO::FETCH_BOTH);
        return [];
    }

}