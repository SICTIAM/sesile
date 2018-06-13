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

    /**
     * @param $userEmail
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLegacyUserRoles($userEmail)
    {
        $query = "SELECT ur.userRoles FROM UserRole ur JOIN User u on ur.user = u.id WHERE u.email = :userEmail";

        return $this->fetchAllData($this->prepareQuery($query, ['userEmail' => $userEmail]), \PDO::FETCH_COLUMN);
    }
}