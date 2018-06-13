<?php


namespace Sesile\MigrationBundle\Service;

use Doctrine\DBAL\Driver\Statement;

/**
 * Class BaseLegacyService
 * @package Sesile\MigrationBundle\Service
 */
class BaseLegacyService
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
     * @param string $query
     * @param array  $parameters ex: ['collectivityId' => $collectivityId]
     *
     * @return Statement The prepared statement.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareQuery($query, array $parameters = [])
    {
        $stmt = $this->connection->prepare($query);
        foreach ($parameters as $key => $value) {
            $stmt->bindParam(':'.$key, $value);
        }
        $stmt->execute();

        return $stmt;
    }

    /**
     * @param Statement $statement
     * @param int $fetchMode
     * @return array
     */
    protected function fetchAllData(Statement $statement, $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $statement->fetchAll($fetchMode);
    }

    /**
     * @param Statement $statement
     * @param int $fetchMode
     * @return mixed
     */
    protected function fetchData(Statement $statement, $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $statement->fetch($fetchMode);
    }

}