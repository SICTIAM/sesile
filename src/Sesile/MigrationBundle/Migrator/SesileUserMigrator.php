<?php


namespace Sesile\MigrationBundle\Migrator;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SesileUserMigrator
 * @package Sesile\MigrationBundle\Migrator
 */
class SesileUserMigrator implements SesileMigratorInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var CollectiviteManager
     */
    protected $collectiviteManager;
    /**
     * @var array
     */
    protected $config;

    /**
     * SesileUserMigrator constructor.
     * @param ClientInterface $client
     * @param CollectiviteManager $collectiviteManager
     * @param array $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        CollectiviteManager $collectiviteManager,
        array $config,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->collectiviteManager = $collectiviteManager;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param $collectivityId
     * @return Message
     */
    public function exportCollectivityUsers($collectivityId)
    {
        try {
            //get collectivity users
            $userRequest = $this->collectiviteManager->getCollectivityUsersList($collectivityId);
            if (false === $userRequest->isSuccess()) {
                $msg = sprintf('Error while retrieving users of collectivity id: %s', $collectivityId);
                $this->logger->error($msg);

                return new Message(false, null, array_merge([$msg], $userRequest->getErrors()));
            }
            $users = $userRequest->getData();
            if (count($users) < 1) {
                $msg = sprintf('No users found for collectivity id: %s. No User Export will be made.', $collectivityId);
                $this->logger->debug($msg);

                return new Message(false, null, [$msg]);
            }
            $requestOptions = $this->buildRequestData($users);
            $response = $this->client->request('POST', 'uri', $requestOptions);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                return new Message(true, $requestOptions);
            }
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers for collectivityid: %s Failed :: %s',
                $collectivityId,
                $response->getBody()->getContents()
            );
            $this->logger->warning($msg);

            return new Message(false, $requestOptions, [$msg]);
        } catch (\Exception $e) {
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers WARNING for collectivityid: %s :: %s',
                $collectivityId,
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers GuzzleException WARNING for collectivityid: %s :: %s',
                $collectivityId,
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        }
    }

    /**
     * @param array $users
     * @return array
     */
    private function buildRequestData(array $users = [])
    {
        $emailArray = array_map(
            function ($user) {
                return $user['email'];
            },
            $users
        );
        $body = [
            "emails" => $emailArray,
            "ozwilloInstanceInfo" => [
                "organizationId" => null,
                "instanceId" => null,
                "creatorId" => null,
                "serviceId" => null,
            ],
        ];
        $requestOptions = [
            'auth' => [$this->config['username'], $this->config['password']],
            'json' => $body
        ];

        return $requestOptions;
    }
}