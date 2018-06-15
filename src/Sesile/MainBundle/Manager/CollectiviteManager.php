<?php


namespace Sesile\MainBundle\Manager;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;

/**
 * Class CollectiviteManager
 * @package Sesile\MainBundle\Manager
 */
class CollectiviteManager
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CollectiviteManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Get an array of all organisations (aka collectivité)
     *
     * @return Message
     */
    public function getCollectivitesList()
    {
        try {
            $data = $this->em->getRepository(Collectivite::class)->getCollectivitesList();

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getCollectivitesList error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }


    /**
     * check if user has a collectivity.
     * based on the ozwillo collectivite client_id
     *
     * @param $userId
     * @param $ozwilloCollectivityClientId
     *
     * @return Message
     */
    public function userHasOzwilloCollectivity($userId, $ozwilloCollectivityClientId)
    {
        try {
            $data = $this->em->getRepository(CollectiviteOzwillo::class)->userHasOzwilloCollectivity(
                $userId,
                $ozwilloCollectivityClientId
            );

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/userHasOzwilloCollectivity error: %s', $e->getMessage()));

            return new Message(false, false, [$e->getMessage()]);
        }
    }

    /**
     * @param $clientId unique ozwillo collectivite id
     *
     * @return Message
     */
    public function getOzwilloCollectivityByClientId($clientId)
    {
        try {
            $data = $this->em->getRepository(CollectiviteOzwillo::class)->findOneByClientId($clientId);

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getOzwilloCollectivityByClientId error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }

    }

    /**
     * @param $siren
     *
     * @return Message
     */
    public function getCollectiviteBySiren($siren)
    {
        try {
            $data = $this->em->getRepository(Collectivite::class)->findOneBy(['siren'=> $siren]);

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getCollectiviteBySiren error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * @param Collectivite $collectivite
     *
     * @return Message
     */
    public function saveCollectivity(Collectivite $collectivite)
    {
        try {
            $this->em->persist($collectivite);
            $this->em->flush();

            return new Message(true, $collectivite);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/saveCollectivity error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * Get an array of all organisations (aka collectivité) list that are not yet migrated
     * see Entity: SesileMigration
     *
     * @return Message
     */
    public function getMigrationCollectivityList()
    {
        try {
            $data = $this->em->getRepository(Collectivite::class)->getMigrationCollectivityList();

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getCollectivitesList error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * @param int $collectivityId
     *
     * @return Message
     */
    public function getCollectivity($collectivityId)
    {
        try {
            $data = $this->em->getRepository(Collectivite::class)->find($collectivityId);
            if ($data && $data instanceof Collectivite) {
                return new Message(true, $data);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getCollectivity error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }

        return new Message(false, null, [sprintf('No collectivity found with id: %s', $collectivityId)]);
    }

    /**
     * @param Collectivite $collectivityFrom
     * @param Collectivite $collectivityTo
     * @return Message
     */
    public function switchCollectivityOzwillo(Collectivite $collectivityFrom, Collectivite $collectivityTo)
    {
        try {
            $data = $this->em->getRepository(CollectiviteOzwillo::class)->switchCollectivityId($collectivityFrom->getId(), $collectivityTo->getId());
            if (true === $data) {
                $msg = sprintf(
                    'CollectiviteManager]/switchCollectivityOzwillo Switch Ozwillo from collectivityId %s to collectivityId %s',
                    $collectivityFrom->getId(),
                    $collectivityTo->getId()
                );
                $this->logger->info($msg);
                return new Message(true, $data);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/switchCollectivityOzwillo error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }

        $msg = sprintf(
            'Switch Ozwillo from collectivityId %s to collectivityId %s Failed',
            $collectivityFrom->getId(),
            $collectivityTo->getId()
        );

        return new Message(false, null, [$msg]);
    }

}