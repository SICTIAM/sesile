<?php


namespace Sesile\ApiBundle\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Class OzwilloProvisioner
 * @package Sesile\ApiBundle\Service
 */
class OzwilloProvisioner
{
    const SERVICE_LOCAL_ID = "sesile";
    const SESILE_ICON_URI = 'https://www.ozwillo.com/static/img/editors/sesile-icon-64x64.png';
    const OZWILLO_CONNECT_URI = 'connect/ozwillo';
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var CollectiviteManager
     */
    protected $collectivityManager;
    /**
     * @var string
     */
    protected $contactEmail;

    /**
     * OzwilloProvisioner constructor.
     * @param Client $client
     * @param CollectiviteManager $collectiviteManager
     * @param Router $router
     * @param string $domain domain parameter
     * @param string $contactEmail
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $client,
        CollectiviteManager $collectiviteManager,
        Router $router,
        $domain,
        $contactEmail,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->collectivityManager = $collectiviteManager;
        $this->router = $router;
        $this->domain = $domain;
        $this->contactEmail = $contactEmail;
        $this->logger = $logger;
    }

    /**
     * This method is called after the call from Ozwillo Provisioning. Sesile created the collectivity and then calls back
     * ozwillo in order to notify that the provisioning worked.
     * Ozwillo returns the serviceId of the sesile service created.
     * Sesile then updates the table collectivity_ozwillo and sets notifiedToKernel to TRUE and the serviceId
     *
     * @param Collectivite $collectivite
     *
     * @return Message
     */
    public function notifyRegistrationToKernel(Collectivite $collectivite)
    {
        try {
            if (!$collectivite->getOzwillo() || !$collectivite->getOzwillo()->getInstanceRegistrationUri()) {
                $msg = sprintf(
                    '[OzwilloProvisioner]/notifyRegistrationToKernel No Instance Registration Uri found for collectivity id: %s',
                    $collectivite->getId()
                );
                $this->logger->warning($msg);

                return new Message(false, null, [$msg]);
            }
            $requestData = $this->buildRequestData($collectivite);
            $instanceRegistrationUri = $collectivite->getOzwillo()->getInstanceRegistrationUri();
            $response = $this->client->request('POST', $instanceRegistrationUri, $requestData);
            $body = json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() !== Response::HTTP_CREATED) {
                $msg = '';
                if (isset($body['error'])) {
                    $msg = $body['error'];
                }

                return new Message(false, $requestData, [$msg]);
            }

            $localId = $this->getLocalId($collectivite);

            if (!isset($body[$localId])) {
                $msg = sprintf(
                    'No service Id returned for Local Id: %s, for Collectivity id %s',
                    $localId,
                    $collectivite->getId()
                );

                return new Message(false, $requestData, [$msg]);
            }

            $serviceId = $body[$localId];
            $result = $this->collectivityManager->updateNotifiedToKernel($collectivite, $serviceId, true);
            if (false === $result->isSuccess()) {
                $msg = sprintf(
                    '[OzwilloProvisioner]/notifyRegistrationToKernel Error on updatingNotificationToKernel for collectivity id: %s and serviceId: %s',
                    $collectivite->getId(),
                    $serviceId
                );
                $this->logger->error($msg);

                return new Message(false, $requestData, [$msg]);
            }

            return new Message(true, $requestData);

        } catch (\Exception $e) {
            $msg = sprintf(
                '[OzwilloProvisioner]/notifyRegistrationToKernel WARNING for collectivityid: %s :: %s',
                $collectivite->getId(),
                $e->getMessage()
            );
            $this->logger->error($msg);

            return new Message(false, null, [$msg]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $msg = sprintf(
                '[OzwilloProvisioner]/notifyRegistrationToKernel GuzzleException WARNING for collectivityid: %s :: %s',
                $collectivite->getId(),
                $e->getMessage()
            );
            $this->logger->error($msg);

            return new Message(false, null, [$msg]);
        }


    }

    /**
     * @param Collectivite $collectivite
     *
     * @return string
     */
    private function getLocalId(Collectivite $collectivite)
    {
        return self::SERVICE_LOCAL_ID.'-'.$collectivite->getDomain();
    }

    /**
     * @param Collectivite $collectivite
     * @return array
     */
    private function buildRequestData(Collectivite $collectivite)
    {
        $contacts = ['mailto:'.$this->contactEmail];
        $services[] = [
            'local_id' => $this->getLocalId($collectivite),
            'name' => sprintf("SESILE - %s", $collectivite->getNom()),
            'tos_uri' => "https://sesile.fr/tos",
            'policy_uri' => "https://sesile.fr/policy",
            'icon' => self::SESILE_ICON_URI,
            'contacts' => $contacts,
            'payment_option' => "PAID",
            'target_audience' => ["PUBLIC_BODIES"],
            'visibility' => "VISIBLE",
            'access_control' => "RESTRICTED",
            'service_uri' => $this->urlRegistrationToKernel(
                $collectivite,
                $this->router->generate('sesile_main_default_app')
            ) . self::OZWILLO_CONNECT_URI,
            'redirect_uris' => [
                $this->urlRegistrationToKernel(
                    $collectivite,
                    $this->router->generate('ozwillo_login')
                ),
            ],
        ];
        $collectiviteOzwillo = $collectivite->getOzwillo();
        $data = [
            'instance_id' => $collectiviteOzwillo->getInstanceId(),
            'destruction_uri' => $this->router->generate(
                'sesile_api_collectivite_delete',
                array('id' => $collectivite->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'destruction_secret' => $collectiviteOzwillo->getDestructionSecret(),
            'status_changed_uri' => $this->router->generate(
                'sesile_api_collectivite_update',
                array('id' => $collectivite->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'status_changed_secret' => $collectiviteOzwillo->getStatusChangedSecret(),
            'services' => $services,
        ];

        $requestOptions = [
            'auth' => [$collectiviteOzwillo->getClientId(), $collectiviteOzwillo->getClientSecret()],
            'json' => $data,
        ];

        return $requestOptions;
    }

    /**
     * @param Collectivite $collectivite
     * @param $path
     * @return string
     */
    private function urlRegistrationToKernel(Collectivite $collectivite, $path)
    {
        return 'https://'.$collectivite->getDomain().'.'.$this->domain.$path;
    }

}