<?php

namespace Sesile\MainBundle\Repository;

use Sesile\MainBundle\Entity\Collectivite;

/**
 * CollectiviteOzwilloRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CollectiviteOzwilloRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * check if user has a collectivity.
     * based on the ozwillo collectivite client_id
     *
     * @param $userId
     * @param $ozwilloCollectivityClientId
     *
     * @return bool
     */
    public function userHasOzwilloCollectivity($userId, $ozwilloCollectivityClientId)
    {
        $result = $this
            ->createQueryBuilder('O')
            ->select('U.id as userId, U.username, C.id as collectivityId, O.clientId')
            ->leftJoin('O.collectivite', 'C')
            ->join('C.users', 'U')
            ->where('O.clientId = :clientId')
            ->andWhere('U.id = :userId')
            ->setParameter('clientId', $ozwilloCollectivityClientId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getArrayResult();
        if (count($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $fromCollectivityId
     * @param $toCollectivityId
     *
     * @return bool
     */
    public function switchCollectivityId($fromCollectivityId, $toCollectivityId)
    {
        try {
            $connection = $this->getEntityManager()->getConnection();
            $connection->beginTransaction();
            $sql = 'UPDATE collectivite_ozwillo co set co.collectivite_id = :toCollectivityId WHERE co.collectivite_id = :fromCollectivityId';
            $result = $connection->executeQuery(
                $sql,
                ['toCollectivityId' => $toCollectivityId, 'fromCollectivityId' => $fromCollectivityId]
            );
            if ($result) {
                $connection->commit();

                return true;
            }
            $connection->rollBack();
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * update the field notifiedToKernel bollean and sets the service Id that is returned by the registration_uri of ozwillo
     *
     * @param string $collectivityId
     * @param $serviceId
     * @param bool $notified
     *
     * @return bool
     */
    public function updateNotifiedToKernel($collectivityId, $serviceId, $notified = true)
    {
        try {
            $connection = $this->getEntityManager()->getConnection();
            $connection->beginTransaction();
            $sql = 'UPDATE collectivite_ozwillo co set co.notifiedToKernel = :notified, co.serviceId = :serviceId WHERE co.collectivite_id = :collectivityId';
            $result = $connection->executeQuery(
                $sql,
                ['notified' => (bool)$notified, 'serviceId' => $serviceId, 'collectivityId' => $collectivityId]
            );
            if ($result) {
                $connection->commit();

                return true;
            }
            $connection->rollBack();
        } catch (\Exception $e) {
            var_dump($e->getMessage());

            return false;
        }

        return false;
    }
}
