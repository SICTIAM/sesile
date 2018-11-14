<?php

namespace Sesile\ApiBundle\EventListener;

use Sesile\ApiBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Doctrine\ORM\EntityManager;

class TokenListener
{

    private $em = null;
    private $sc = null;

    /**
     * @var string domain
     */
    protected $ozwilloSecret;


    public function __construct(EntityManager $oEntityManager, TokenStorage $oSecurityContext, $ozwilloSecret)
    {
        $this->em = $oEntityManager;
        $this->sc = $oSecurityContext;
        $this->ozwilloSecret = $ozwilloSecret;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();


        /*
         * $controller peut être une classe ou une closure. Ce n'est pas
         * courant dans Symfony2 mais ça peut arriver.
         * Si c'est une classe, elle est au format array
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TokenAuthenticatedController) {
            $headers = $event->getRequest()->headers;
            if ($headers->has("token") && $headers->has("secret")) {
                $entity = $this->em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $headers->get('token'), 'apisecret' => $headers->get('secret'), 'apiactivated' => true));;

                if (empty($entity)) {
                    throw new AccessDeniedHttpException('Cette action nécessite un couple token - secret valide!');
                }


            } else if ($headers->has("X-Hub-Signature")) {
                $check = false;
                if(!empty($headers->get("X-Hub-Signature"))) {
                    $check = $this->checkSignature($event->getRequest()->getContent(), $headers->get("X-Hub-Signature"), $this->ozwilloSecret);
                }

                if(!$check) {
                    return new JsonResponse("X-Hub-Signature is not valid", Response::HTTP_FORBIDDEN);
                } else if ($check) {
                    return true;
                }

                throw new AccessDeniedHttpException('Token and secret required');
            } else {
                throw new AccessDeniedHttpException('Authentication is required by Token and secret, or signature');
            }


        }
    }

    private function checkSignature(String $requestBody, String $xHubSignature, String $secret) {

        if (substr($xHubSignature, 0, 5) !== "sha1=") {
            return false;
        }

        $signingKey = hash_hmac('sha1', $requestBody, $secret);
        $hMac = explode('=', $xHubSignature);

        if ($signingKey === $hMac[1]) {
            return true;
        }

        return false;

    }
}