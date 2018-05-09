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

}