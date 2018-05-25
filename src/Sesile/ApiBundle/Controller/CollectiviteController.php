<?php

namespace Sesile\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * @Rest\Route("/collectivite")
 */
class CollectiviteController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"getAllCollectivite"})
     * @Rest\Get("s")
     * @return array
     */
    public function getAllAction()
    {

        return $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileMainBundle:Collectivite')
            ->findAll();
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_ACCEPTED")
     * @Rest\Post("/new")
     * @param Request $request
     * @return JsonResponse|Request
     * {"instance_id":"bce53130-af7d-44a0-8a87-291a37f22e4c","destruction_uri":"https://sictiam.stela3-dev.sictiam.fr/api/admin/ozwillo/delete","destruction_secret":"secret","status_changed_uri":"https://sictiam.stela3-dev.sictiam.fr/api/admin/ozwillo/status","status_changed_secret":"secret","services":[{"local_id":"back-office","name":"STELA
     * - SICTIAM","description":"Tiers de télétransmission",
     * "tos_uri":"https://stela.fr/tos","policy_uri":"https://stela.fr/policy","icon":"https://stela.fr/icon.png","contacts":["admin@stela.fr","demat@sictiam.fr"],"payment_option":"PAID","target_audience":"PUBLIC_BODY","visibility":"VISIBLE","access_control":"RESTRICTED","service_uri":"https://sictiam.stela3-dev.sictiam.fr/login","redirect_uris":["https://sictiam.stela3-dev.sictiam.fr/login"]}]}
     *
     */
    public function postAction (Request $request)
    {

        $secret = $this->getParameter('ozwillo_secret');
        if (!$request->headers->has("X-Hub-Signature")) {
            return new JsonResponse("No X-Hub-Signature header found in request", Response::HTTP_BAD_REQUEST);
        } else if (!$this->checkSignature($request->getContent(), $request->headers->get("X-Hub-Signature"), $secret)) {
            return new JsonResponse("X-Hub-Signature is not valid", Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();
        $siren = substr(strrchr($request->get('organization')['dc_id'], "/"), 1, 9);
        $userObject = $request->get('user');
        if (isset($request->get('user')['email_address'])) {
            $user = $em->getRepository('SesileUserBundle:User')->findOneByEmail($request->get('user')['email_address']);
        } else {
            $user = $em->getRepository('SesileUserBundle:User')->findOneByOzwilloId($userObject['id']);
        }

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneBySiren($siren);
        if ($collectivite && $collectivite->getOzwillo() instanceof CollectiviteOzwillo) {
            return new JsonResponse("There already exists a local authority with SIREN", Response::HTTP_BAD_REQUEST);
        } elseif ($collectivite && !$collectivite->getOzwillo()) {
            //create CollectiviteOzwillo
            $collectiviteOzwillo = $this->buildCollectiviteOzwilloFromOzwilloRequest($request, $collectivite);
            $collectivite->setOzwillo($collectiviteOzwillo);
            $em->persist($collectiviteOzwillo);
            $em->persist($collectivite);
            $em->flush();
        } else {
            $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->createCollectiviteFromOzwillo($request);
        }

        $userOzwillo = $em->getRepository('SesileUserBundle:User')->createUserFromOzwillo($user, $userObject, $collectivite);

        $notifyRegistrationToKernel = $this->notifyRegistrationToKernel($collectivite);

        return new JsonResponse($notifyRegistrationToKernel, Response::HTTP_ACCEPTED);
    }

    /**
     * @param Request $request
     * @param Collectivite $collectivite
     *
     * @return CollectiviteOzwillo
     */
    private function buildCollectiviteOzwilloFromOzwilloRequest(Request $request, Collectivite $collectivite)
    {
        $collectiviteOzwillo = new CollectiviteOzwillo();
        $collectiviteOzwillo->setInstanceId($request->get('instance_id'));
        $collectiviteOzwillo->setClientId($request->get('client_id'));
        $collectiviteOzwillo->setClientSecret($request->get('client_secret'));
        $collectiviteOzwillo->setInstanceRegistrationUri($request->get('instance_registration_uri'));
        $collectiviteOzwillo->setDcId($request->get('organization')['dc_id']);
        $collectiviteOzwillo->setServiceId($request->get('instance_id'));
        $collectiviteOzwillo->setDestructionSecret(base64_encode(random_bytes(10)));
        $collectiviteOzwillo->setStatusChangedSecret(base64_encode(random_bytes(10)));
        $collectiviteOzwillo->setCollectivite($collectivite);

        return $collectiviteOzwillo;
    }


    /**
     * @Rest\View("statusCode=Response::HTTP_ACCEPTED")
     * @Rest\Post("/update")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $secret = $this->getParameter('ozwillo_secret');
        if (!$request->headers->has("X-Hub-Signature")) {
            return new JsonResponse("No X-Hub-Signature header found in request", Response::HTTP_BAD_REQUEST);
        } else if (!$this->checkSignature($request->getContent(), $request->headers->get("X-Hub-Signature"), $secret)) {
            return new JsonResponse("X-Hub-Signature is not valid", Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();
        $collectiviteOzwillo = $em->getRepository('SesileMainBundle:CollectiviteOzwillo')->findOneByInstanceId($request->get('instance_id'));

        if ($collectiviteOzwillo instanceof CollectiviteOzwillo) {

            if ($request->get('status') == 'STOPPED') {
                $collectiviteOzwillo->getCollectivite()->setActive(false);
            } elseif ($request->get('status') == 'RUNNING') {
                $collectiviteOzwillo->getCollectivite()->setActive(true);
            }
            $em->flush();
            return new JsonResponse("The instance status has been changed", Response::HTTP_ACCEPTED);
        }

        return new JsonResponse("instance_id is not valid", Response::HTTP_FORBIDDEN);
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_ACCEPTED")
     * @Rest\Delete("/delete")
     * @ParamConverter("Collectivite", options={"mapping": {"id": "id"}})
     * @param Collectivite $collectivite
     */
    public function deleteAction(Collectivite $collectivite)
    {

    }

    private function notifyRegistrationToKernel(Collectivite $collectivite){
        $jsonResponse = new \stdClass();
        $contacts = ['demat@sictiam.fr'];
        $services = [
            'name'      => "SESILE - SICTIAM",
            'tos_uri'   => "https://sesile.fr/tos",
            'policy_uri' => "https://sesile.fr/policy",
            'icon'      => "https://sesile.fr/images/favicons/sesile-icon-64x64.png",
            'contacts'  => $contacts,
            'payment_option' => "PAID",
            'target_audience' => "PUBLIC_BODY",
            'visibility' => "VISIBLE",
            'access_control' => "RESTRICTED",
            'service_uri' => $this->urlRegistrationToKernel($collectivite, $this->generateUrl('sesile_main_default_app')),
            'redirect_uris' => [$this->urlRegistrationToKernel($collectivite, $this->generateUrl('ozwillo_login'))]
        ];
        $jsonResponse->instance_id = $collectivite->getOzwillo()->getInstanceId();
        $jsonResponse->destruction_uri = $this->generateUrl('sesile_api_collectivite_delete', array('id' => $collectivite->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonResponse->destruction_secret = $collectivite->getOzwillo()->getDestructionSecret();
        $jsonResponse->status_changed_uri = $this->generateUrl('sesile_api_collectivite_update', array('id' => $collectivite->getId()), UrlGeneratorInterface::ABSOLUTE_URL);;
        $jsonResponse->status_changed_secret = $collectivite->getOzwillo()->getStatusChangedSecret();
        $jsonResponse->services = $services;

        return $jsonResponse;
    }

    private function urlRegistrationToKernel(Collectivite $collectivite, $path) {
        return 'https://' . $collectivite->getDomain() . '.' . $this->getParameter('domain') . $path;
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
