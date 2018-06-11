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

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCollectivityList()
    {
        $query = "SELECT id, nom as name, domain FROM Collectivite";

        return $this->fetchData($query);
    }

    /**
     * @param string $query
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function fetchData($query)
    {
        return $this->connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

}