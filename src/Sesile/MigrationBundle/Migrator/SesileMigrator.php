<?php


namespace Sesile\MigrationBundle\Migrator;


use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MigrationBundle\Entity\SesileMigration;
use Sesile\MigrationBundle\Manager\SesileMigrationManager;

/**
 * Class SesileMigrator
 * @package Sesile\MigrationBundle\Migrator
 */
class SesileMigrator implements SesileMigratorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CollectiviteManager
     */
    protected $collectivityManager;
    /**
     * @var SesileMigrationManager
     */
    protected $sesileMigrationManager;

    /**
     * SesileMigrator constructor.
     * @param SesileMigrationManager $sesileMigrationManager
     * @param CollectiviteManager $collectiviteManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SesileMigrationManager $sesileMigrationManager,
        CollectiviteManager $collectiviteManager,
        LoggerInterface $logger
    ) {
        $this->sesileMigrationManager = $sesileMigrationManager;
        $this->collectivityManager = $collectiviteManager;
        $this->logger = $logger;
    }

    /**
     * it creates a new entry into the table sesile_migration
     * + it sets the siren to the collectivity
     * + it checks if already the collectivity has been "provisioned"
     * in this case we switch the collectivity ozwillo
     *
     * @param $collectivityId
     * @param $siren
     * @return Message
     */
    public function hanldeNewMigration($collectivityId, $siren)
    {
        $collectivityMsg = $this->collectivityManager->getCollectivity($collectivityId);
        if (false === $collectivityMsg->isSuccess()) {
            $msg = sprintf('Unable to Find Collecitivity with id: %s', $collectivityId);
            $this->logger->debug($msg);

            return new Message(false, null, [$msg]);
        }
        $collectivity = $collectivityMsg->getData();

        $sesileMigration = $this->buildSesileMigration($collectivity, $siren);
        $result = $this->sesileMigrationManager->create($sesileMigration);
        if (false === $result->isSuccess()) {
            return new Message(false, null, $result->getErrors());
        }
        $collectivity->setSiren($siren);
        if (false === $this->collectivityManager->saveCollectivity($collectivity)->isSuccess()) {
            return new Message(false, null, ['Unable to Set the Siren into the collectivity']);
        }
        //find collectivity with the same siren that is already provisioned by ozwillo
        $provisionningResult = $this->handleProvisionedCollectivity($siren, $collectivity);
        if (false === $provisionningResult->isSuccess()) {
            return new Message(false, null, $provisionningResult->getErrors());
        }

        return new Message(true, $sesileMigration);
    }

    /**
     * @param $siren
     * @param Collectivite $collectivity
     * @return Message
     */
    private function handleProvisionedCollectivity($siren, Collectivite $collectivity)
    {
        $provisionedCollectivity = $this->getProvisionedCollectivity($siren);
        if (false === $provisionedCollectivity) {
            //no provisioned collectivity found
            $this->logger->debug(sprintf('[SesileMigrator] No Provisioned Collectivity found for siren: %s', $siren));
            return new Message(true, null, []);
        }
        $result = $this->collectivityManager->switchCollectivityOzwillo($provisionedCollectivity, $collectivity);
        if (false === $result->isSuccess()){
            return new Message(false, null, $result->getErrors());
        }
        //remove siren from provised collectivity
        $provisionedCollectivity->setSiren(null);
        $provisionedCollectivity->setActive(false);
        $result = $this->collectivityManager->saveCollectivity($provisionedCollectivity);
        if (false === $result->isSuccess()){
            $msg = sprintf('Error on Setting Null Siren Field on collectivity Id: %s', $provisionedCollectivity->getId());
            return new Message(false, null, array_merge([$msg], $result->getErrors()));
        }
        //Clear all users of collectivity
        $clearResult = $this->collectivityManager->clearCollectivityUsers($provisionedCollectivity);
        $this->logger->debug(sprintf('[SesileMigrator] Clear Users of Provisioned Collectivity: %s', $provisionedCollectivity->getId()));
        if (true === $clearResult->isSuccess()) {
            $this->collectivityManager->removeCollectivity($provisionedCollectivity);
            $this->logger->debug(sprintf('[SesileMigrator] Remove Provisioned Collectivity: %s', $provisionedCollectivity->getId()));
        }
        return new Message(true, null, []);
    }

    /**
     * @param Collectivite $collectivity
     * @param string $siren
     *
     * @return SesileMigration
     */
    private function buildSesileMigration(Collectivite $collectivity, $siren)
    {
        $sesileMigration = new SesileMigration();
        $sesileMigration->setCollectivityId($collectivity->getId());
        $sesileMigration->setSiren($siren);
        $sesileMigration->setCollectivityName($collectivity->getNom());

        return $sesileMigration;
    }

    /**
     * @param $siren
     * @return bool|mixed
     */
    private function getProvisionedCollectivity($siren)
    {
        $result = $this->collectivityManager->getCollectiviteBySiren($siren);
        if (false === $result->isSuccess() || !$result->getData() instanceof Collectivite) {
            return false;
        }
        //if collectivity has no ozwillo configuration
        if (!$result->getData()->getOzwillo()) {
            return false;
        }
        $msg = sprintf(
            '[SesileMigrator] A Collectivity with Siren: %s found. id: %s ozwillo instanceId: %s',
            $siren,
            $result->getData()->getId(),
            $result->getData()->getOzwillo()->getInstanceId()
        );
        $this->logger->info($msg);

        return $result->getData();
    }

}