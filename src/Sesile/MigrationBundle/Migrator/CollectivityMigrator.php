<?php


namespace Sesile\MigrationBundle\Migrator;


use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MigrationBundle\Service\LegacyCollectivityService;

/**
 * Class CollectivityMigrator
 * @package Sesile\MigrationBundle\Migrator
 */
class CollectivityMigrator implements SesileMigratorInterface
{
    /**
     * @var LegacyCollectivityService
     */
    protected $service;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CollectiviteManager
     */
    protected $collectivityManager;

    /**
     * CollectivityMigrator constructor.
     * @param LegacyCollectivityService $legacyCollectivityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        LegacyCollectivityService $legacyCollectivityService,
        CollectiviteManager $collectiviteManager,
        LoggerInterface $logger
    ) {
        $this->service = $legacyCollectivityService;
        $this->collectivityManager = $collectiviteManager;
        $this->logger = $logger;
    }

    /**
     * @param integer $collectivityId
     * @param string  $siren
     *
     * @return Message
     */
    public function migrate($collectivityId, $siren)
    {
        $this->logger->info(sprintf('[COLLECTIVITY_MIGRATOR] START for legacy collectivity: %s', $collectivityId));
        if ($siren == '') {
            return new Message(false, null, [sprintf('[COLLECTIVITY_MIGRATOR] EMPTY SIREN IS GIVEN for Legacy Collectivity with id: %s not found.', $collectivityId)]);
        }
        $legacyCollectivity = $this->service->getLegacyCollectivity($collectivityId);
        if (!$legacyCollectivity) {
            return new Message(false, null, [sprintf('[COLLECTIVITY_MIGRATOR] Legacy Collectivity with id: %s not found.', $collectivityId)]);
        }
        $collectivity = $this->buildCollectivity($legacyCollectivity, $siren);
        $result = $this->collectivityManager->saveCollectivity($collectivity);
        if (false === $result->isSuccess()) {
            return new Message(false, null, [sprintf('[COLLECTIVITY_MIGRATOR] Legacy Collectivity with id: %s Could not be saved.', $collectivityId)]);
        }

        return new Message(true, $result->getData());
    }

    /**
     * @param array   $legacyCollectivity
     * @param string  $siren
     *
     * @return Collectivite
     */
    private function buildCollectivity(array $legacyCollectivity = [], $siren)
    {
        $collectivity = new Collectivite();
        $collectivity
            ->setNom($legacyCollectivity['nom'])
            ->setDomain($legacyCollectivity['domain'])
            ->setImage($legacyCollectivity['domain'])
            ->setImage($legacyCollectivity['image'])
            ->setMessage($legacyCollectivity['message'])
            ->setActive($legacyCollectivity['active'])
            ->setTextmailnew($legacyCollectivity['textmailnew'])
            ->setTextmailrefuse($legacyCollectivity['textmailrefuse'])
            ->setTextmailwalid($legacyCollectivity['textmailwalid'])
            ->setAbscissesVisa($legacyCollectivity['abscissesVisa'])
            ->setOrdonneesVisa($legacyCollectivity['ordonneesVisa'])
            ->setAbscissesSignature($legacyCollectivity['abscissesSignature'])
            ->setOrdonneesSignature($legacyCollectivity['ordonneesSignature'])
            ->setCouleurVisa($legacyCollectivity['couleurVisa'])
            ->setTitreVisa($legacyCollectivity['titreVisa'])
            ->setPageSignature($legacyCollectivity['pageSignature'])
            ->setDeleteClasseurAfter($legacyCollectivity['deleteClasseurAfter'])
            ->setSiren(substr(trim($siren), 0, 9));
        ;

        return $collectivity;

    }
}