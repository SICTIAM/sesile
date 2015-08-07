<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository {

    public function findByUserPacks($userPack) {

        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.userPacks', 'u')
            ->where('u.id = :userid')
            ->setParameter('userid', $userPack)
            ->getQuery()
            ->getResult();
    }
}
