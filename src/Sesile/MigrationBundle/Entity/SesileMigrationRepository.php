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
            ->select('sm.id','sm.collectivityId','sm.collectivityName','sm.siren','sm.status','sm.usersExported','sm.date', 'sm.oldId')
            ->leftJoin('SesileMainBundle:CollectiviteOzwillo', 'o', 'WITH', 'o.collectivite = sm.collectivityId')
            ->addSelect('o.instanceId', 'o.serviceId')
            ->addOrderBy('sm.date', 'DESC')
            ->getQuery()
            ->getArrayResult()
            ;
    }
}
