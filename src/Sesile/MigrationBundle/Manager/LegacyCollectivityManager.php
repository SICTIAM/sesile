<?php


namespace Sesile\MigrationBundle\Manager;


use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Service\LegacyCollectivityService;

class LegacyCollectivityManager
{
    /**
     * @var LegacyCollectivityService
     */
    protected $collectivityService;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * LegacyCollectivityManager constructor.
     * @param LegacyCollectivityService $legacyCollectivityService
     */
    public function __construct(LegacyCollectivityService $legacyCollectivityService, LoggerInterface $logger)
    {
        $this->collectivityService = $legacyCollectivityService;
        $this->logger = $logger;
    }

    /**
     * @return Message
     */
    public function getLegacyCollectivityList()
    {
        try {
            $data = $this->collectivityService->getCollectivityList();
            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[LegacyCollectivityManager]/getLegacyCollectivityList error: %s', $e->getMessage()));
            return new Message(false, null);
        }
    }
}