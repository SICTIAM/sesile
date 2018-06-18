<?php

namespace Sesile\MigrationBundle\Entity;

/**
 * Class SesileMigrationRepository
 * @package Sesile\MigrationBundle\Repository
 */
class SesileMigrationRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function getSesileMigrationHistory()
    {
        return $this
            ->createQueryBuilder('sm')
            ->getQuery()
            ->getArrayResult();
    }
}
