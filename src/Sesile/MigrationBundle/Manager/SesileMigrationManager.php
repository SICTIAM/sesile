<?php


namespace Sesile\MigrationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\MainBundle\Manager\SesileMailer;
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
     * @var SesileMailer
     */
    protected $mailer;
    /**
     * @var string
     */
    protected $contactEmail;

    /**
     * SesileMigrationManager constructor.
     * @param EntityManager $em
     * @param SesileMailer $mailer
     * @param string $contactEmail
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, SesileMailer $mailer, $contactEmail, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->contactEmail = $contactEmail;
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

    /**
     * @param Collectivite $collectivity
     *
     * @return Message
     */
    public function finish(Collectivite $collectivity)
    {
        try {
            $sesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
                ['collectivityId' => $collectivity->getId()]
            );
            if (!$sesileMigration instanceof SesileMigration) {
                $msg = sprintf(
                    '[SesileMigrationManager]/finish Sesile Migration Not found for Collectivity id: %s',
                    $collectivity->getId()
                );

                return new Message(false, null, [$msg]);
            }
            $sesileMigration->setStatus(SesileMigration::STATUS_FINALISE);
            $sesileMigration->setUsersExported(true);
            $this->em->persist($sesileMigration);
            $this->em->flush();
            //send email
            $this->sentEmailConfirmationMigration($sesileMigration);

            return new Message(true, $sesileMigration);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[SesileMigrationManager]/finish error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * @return Message
     */
    public function getSesileMigrationHistory()
    {
        try {
            $result = $this->em->getRepository(SesileMigration::class)->getSesileMigrationHistory();

            return new Message(true, $this->handleMigrationData($result));
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[SesileMigrationManager]/getSesileMigrationHistory error: %s', $e->getMessage())
            );

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * @param Collectivite $collectivite
     * @return Message
     */
    public function allowOzwilloUserExport(Collectivite $collectivite)
    {
        try {
            $ozwillo = $collectivite->getOzwillo();
            if (!$ozwillo instanceof CollectiviteOzwillo) {
                $msg = sprintf(
                    '[SesileMigrationManager]/allowOzwilloUserExport Collectivity id: %s, has no ozwillo configuration.',
                    $collectivite->getId()
                );
                $this->logger->warning($msg);

                return new Message(false, $collectivite, [$msg]);
            }
            //find the SesileMigration for the collectivity
            $sesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
                ['collectivityId' => $collectivite->getId()]
            );
            if (!$sesileMigration instanceof SesileMigration) {
                $msg = sprintf(
                    '[SesileMigrationManager]/allowOzwilloUserExport No Sesile Migration entry found for Collectivity id: %s.',
                    $collectivite->getId()
                );
                $this->logger->warning($msg);

                return new Message(false, $collectivite, [$msg]);
            }
            if (true === $this->allowExportByConditions(
                    $ozwillo->getInstanceId(),
                    $ozwillo->getServiceId(),
                    $sesileMigration->hasUsersExported()
                )) {
                return new Message(true, $collectivite);
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[SesileMigrationManager]/getSesileMigrationHistory error: %s', $e->getMessage())
            );

            return new Message(false, null, [$e->getMessage()]);
        }

        return new Message(false, null, []);
    }

    /**
     * @param array $migrationData
     * @return array
     */
    private function handleMigrationData(array $migrationData)
    {
        if (count($migrationData) == 0) {
            return [];
        }
        $data = [];
        foreach ($migrationData as $item) {
            $item['allowExport'] = 0;
//            if ($item['instanceId'] && $item['serviceId'] && true !== (bool)$item['usersExported']) {
            if (true === $this->allowExportByConditions(
                    $item['instanceId'],
                    $item['serviceId'],
                    $item['usersExported']
                )) {
                $item['allowExport'] = 1;
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param $instanceId
     * @param $serviceId
     * @param $userExported
     * @return bool
     */
    private function allowExportByConditions($instanceId, $serviceId, $userExported)
    {
        if ($instanceId != '' && $serviceId != '' && true !== (bool)$userExported) {
            return true;
        }

        return false;
    }

    /**
     * @param SesileMigration $migration
     *
     * @return Message
     */
    private function sentEmailConfirmationMigration(SesileMigration $migration)
    {
        return $this->mailer->send(
            [$this->contactEmail],
            '[Sesile] La migration a bien été effectué',
            $migration,
            '@SesileMigration/mail/confirmationMigration.html.twig'
        );
    }

}