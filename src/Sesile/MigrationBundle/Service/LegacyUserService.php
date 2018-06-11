<?php


namespace Sesile\MigrationBundle\Service;

/**
 * Class LegacyUserService
 * @package Sesile\MigrationBundle\Service
 */
class LegacyUserService extends BaseLegacyService
{
    /**
     * @param $collectivityId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLegacyUsersByCollectivity($collectivityId)
    {
        $query = "SELECT * FROM User WHERE collectivite = :collectivityId";

        return $this->fetchAllData($this->prepareQuery($query, ['collectivityId' => $collectivityId]));
    }
}