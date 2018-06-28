<?php


namespace Sesile\UserBundle\Service;


use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Sesile\MainBundle\Domain\Message;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OzwilloUserService
 * @package Sesile\UserBundle\Service
 */
class OzwilloUserService
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
     * @var string
     */
    protected $ozwilloAclInstanceUri;

    /**
     * OzwilloUserMigrator constructor.
     * @param ClientInterface $client
     * @param string $ozwilloAclInstanceUri
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $client, $ozwilloAclInstanceUri, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->ozwilloAclInstanceUri = $ozwilloAclInstanceUri;
        $this->logger = $logger;
    }

    /**
     * @param $instanceId
     * @param $userAccessToken
     * @param bool $array
     *
     * @return Message
     */
    public function getOzwilloAclInstance($instanceId, $userAccessToken, $array = false)
    {
        try {
            $response = $this->client->request(
                'GET',
                $this->ozwilloAclInstanceUri.'/'.$instanceId,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$userAccessToken,
                    ],
                ]
            );
            if ($response->getStatusCode() === Response::HTTP_OK) {
                if (true === $array) {
                    return new Message(true, json_decode($response->getBody()->getContents(), true));
                }

                return new Message(true, $response->getBody()->getContents());
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $msg = sprintf(
                '[OzwilloUserService]/getOzwilloAclInstance GuzzleException WARNING for instanceId: %s CODE: %s :: %s',
                $e->getCode(),
                $instanceId,
                $e->getMessage()
            );
            $this->logger->error($msg);

            return new Message(false, $e->getCode(), [$e->getMessage()]);
        } catch (\Exception $e) {
            $msg = sprintf(
                '[OzwilloUserService]/getOzwilloAclInstance Exception WARNING for instanceId: %s :: %s',
                $instanceId,
                $e->getMessage()
            );
            $this->logger->error($msg);

            return new Message(false, ['code' => $e->getCode()], [$e->getMessage()]);
        }
    }

}