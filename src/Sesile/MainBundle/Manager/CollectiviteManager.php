<?php


namespace Sesile\MainBundle\Manager;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Entity\Collectivite;

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
     * @return array
     */
    public function getCollectivitesList()
    {
        try {
            return $this->em->getRepository(Collectivite::class)->getCollectivitesList();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[CollectiviteManager]/getCollectivitesList error: %s', $e->getMessage()));
            return [];
        }

    }

}