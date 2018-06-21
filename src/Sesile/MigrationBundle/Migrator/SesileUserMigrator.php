<?php


namespace Sesile\MigrationBundle\Migrator;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
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
     * @param Collectivite $collectivity
     * @return Message
     */
    public function exportCollectivityUsers(Collectivite $collectivity)
    {
        try {
            $collectivityOzwillo = $collectivity->getOzwillo();
            if (!$collectivityOzwillo instanceof CollectiviteOzwillo) {
                $msg = sprintf(
                    '[SesileUserMigrator]/exportCollectivityUsers Collectivity id: %s has no Collectivity Ozwillo Configuration',
                    $collectivity->getId()
                );
                $this->logger->warning($msg);

                return new Message(false, null, [$msg]);
            }
            //get collectivity users
            $userRequest = $this->collectiviteManager->getCollectivityUsersList($collectivity->getId());
            if (false === $userRequest->isSuccess()) {
                $msg = sprintf(
                    '[SesileUserMigrator]/exportCollectivityUsers Error while retrieving users of collectivity id: %s',
                    $collectivity->getId()
                );
                $this->logger->error($msg);

                return new Message(false, null, array_merge([$msg], $userRequest->getErrors()));
            }
            $users = $userRequest->getData();
            if (count($users) < 1) {
                $msg = sprintf(
                    '[SesileUserMigrator]/exportCollectivityUsers No users found for collectivity id: %s. No User Export will be made.',
                    $collectivity->getId()
                );
                $this->logger->debug($msg);

                return new Message(false, null, [$msg]);
            }

            $requestOptions = $this->buildRequestData($collectivityOzwillo, $users);
            $response = $this->client->request('POST', $this->config['gateway_uri'], $requestOptions);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                return new Message(true, $requestOptions);
            }
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers for collectivityid: %s Failed :: %s',
                $collectivity->getId(),
                $response->getBody()->getContents()
            );
            $this->logger->warning($msg);

            return new Message(false, $requestOptions, [$msg]);
        } catch (\Exception $e) {
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers WARNING for collectivityid: %s :: %s',
                $collectivity->getId(),
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $msg = sprintf(
                '[SesileUserMigrator]/exportCollectivityUsers GuzzleException WARNING for collectivityid: %s :: %s',
                $collectivity->getId(),
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        }
    }

    /**
     * @param CollectiviteOzwillo $collectivityOzwillo
     * @param array $users
     * @return array
     */
    private function buildRequestData(CollectiviteOzwillo $collectivityOzwillo, array $users = [])
    {
        $emailArray = array_map(
            function ($user) {
                return $user['email'];
            },
            $users
        );
        $adminUserOzwilloId = $this->findAdminUserOzwilloId($users);
        $body = [
            "emails" => $emailArray,
            "ozwilloInstanceInfo" => [
                "organizationId" => $collectivityOzwillo->getOrganizationId(),
                "instanceId" => $collectivityOzwillo->getInstanceId(),
                "creatorId" => $adminUserOzwilloId,
                "serviceId" => $collectivityOzwillo->getServiceId(),
            ],
        ];
        $requestOptions = [
            'auth' => [$this->config['username'], $this->config['password']],
            'json' => $body,
        ];

        return $requestOptions;
    }

    /**
     * @param $users
     * @return string|null
     */
    private function findAdminUserOzwilloId($users)
    {
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user['roles']) && $user['ozwilloId'] != ''){
                return $user['ozwilloId'];
            }
        }

        return null;
    }
}