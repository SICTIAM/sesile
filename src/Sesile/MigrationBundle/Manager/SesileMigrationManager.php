<?php


namespace Sesile\MigrationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Entity\SesileMigration;

/**
 * Class SesileMigrationManager
 * @package Sesile\MigrationBundle\Manager
 */
class SesileMigrationManager
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
     * SesileMigrationManager constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param SesileMigration $migration
     * @return Message
     */
    public function create(SesileMigration $migration)
    {
        try {
            $this->em->persist($migration);
            $this->em->flush();

            return new Message(true, $migration);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[SesileMigrationManager]/create error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

}