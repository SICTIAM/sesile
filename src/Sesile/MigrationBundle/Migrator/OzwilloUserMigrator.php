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
use Sesile\MigrationBundle\Domain\MigrationReport;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OzwilloUserMigrator
 * @package Sesile\MigrationBundle\Migrator
 */
class OzwilloUserMigrator implements SesileMigratorInterface
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
     * OzwilloUserMigrator constructor.
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
                    '[OzwilloUserMigrator]/exportCollectivityUsers Collectivity id: %s has no Collectivity Ozwillo Configuration',
                    $collectivity->getId()
                );
                $this->logger->warning($msg);

                return new Message(false, null, [$msg]);
            }
            //get collectivity users
            $userRequest = $this->collectiviteManager->getCollectivityUsersList($collectivity->getId());
            if (false === $userRequest->isSuccess()) {
                $msg = sprintf(
                    '[OzwilloUserMigrator]/exportCollectivityUsers Error while retrieving users of collectivity id: %s',
                    $collectivity->getId()
                );
                $this->logger->error($msg);

                return new Message(false, null, array_merge([$msg], $userRequest->getErrors()));
            }
            $users = $userRequest->getData();
            if (count($users) < 1) {
                $msg = sprintf(
                    '[OzwilloUserMigrator]/exportCollectivityUsers No users found for collectivity id: %s. No User Export will be made.',
                    $collectivity->getId()
                );
                $this->logger->debug($msg);

                return new Message(false, null, [$msg]);
            }

            $requestOptions = $this->buildRequestData($collectivityOzwillo, $users);
            $response = $this->client->request('POST', $this->config['gateway_uri'], $requestOptions);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $migrationReport = $this->buildMigrationReport(
                    $users,
                    $collectivityOzwillo,
                    $requestOptions['json']['ozwilloInstanceInfo']['creatorId']
                );
                return new Message(true, $migrationReport);
            }
            $msg = sprintf(
                '[OzwilloUserMigrator]/exportCollectivityUsers for collectivityid: %s Failed :: %s',
                $collectivity->getId(),
                $response->getBody()->getContents()
            );
            $this->logger->warning($msg);

            return new Message(false, $requestOptions, [$msg]);
        } catch (\Exception $e) {
            $msg = sprintf(
                '[OzwilloUserMigrator]/exportCollectivityUsers WARNING for collectivityid: %s :: %s',
                $collectivity->getId(),
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $msg = sprintf(
                '[OzwilloUserMigrator]/exportCollectivityUsers GuzzleException WARNING for collectivityid: %s :: %s',
                $collectivity->getId(),
                $e->getMessage()
            );
            $this->logger->warning($msg);

            return new Message(false, null, [$msg]);
        }
    }

    /**
     * @param array $users
     * @param CollectiviteOzwillo $collectiviteOzwillo
     * @param string $creatorId
     *
     * @return MigrationReport
     */
    private function buildMigrationReport(array $users, CollectiviteOzwillo $collectiviteOzwillo, $creatorId)
    {
        return new MigrationReport($users, $collectiviteOzwillo, $creatorId);
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