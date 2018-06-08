<?php


namespace Sesile\MigrationBundle\Service;

/**
 * Class LegacyCollectivityService
 * @package Sesile\MigrationBundle\Service
 */
class LegacyCollectivityService extends BaseLegacyService
{
    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCollectivityList()
    {
        $query = "SELECT id, nom as name, domain FROM Collectivite";

        return $this->fetchAllData($this->prepareQuery($query));
    }

    public function getLegacyCollectivity($collectivityId)
    {
        $query = 'SELECT * FROM Collectivite where id = :collectivityId';

        return $this->fetchData($this->prepareQuery($query, ['collectivityId' => $collectivityId]));
    }

}