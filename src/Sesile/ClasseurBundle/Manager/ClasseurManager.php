<?php


namespace Sesile\ClasseurBundle\Manager;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\ClasseurBundle\Domain\SearchClasseurData;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;

/**
 * Class ClasseurManager
 * @package Sesile\ClasseurBundle\Manager
 */
class ClasseurManager
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
    public function searchClasseurs(Collectivite $collectivity, User $user, SearchClasseurData $classeurData)
    {
        try {
            $data = $this->em->getRepository(Classeur::class)->searchClasseurs($collectivity->getId(), $user->getId(), $classeurData->getName());

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[ClasseurManager]/searchClasseurs error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

}