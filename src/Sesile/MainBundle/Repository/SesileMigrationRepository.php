<?php

namespace Sesile\MainBundle\Repository;

/**
 * Class SesileMigrationRepository
 * @package Sesile\MainBundle\Repository
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
