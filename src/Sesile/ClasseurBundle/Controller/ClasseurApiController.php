<?php

namespace Sesile\ClasseurBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ApiBundle\Controller\TokenAuthenticatedController;
use Sesile\ClasseurBundle\Domain\SearchClasseurData;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Form\ClasseurPostType;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Manager\ClasseurManager;
use Sesile\ClasseurBundle\Service\ActionMailer;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Rest\Route("/api/v4", options = { "expose" = true })
 */
class ClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("/org/{orgId}/classeurs/list/all")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listAllAction($orgId)
    {
        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getAllClasseursVisibles($orgId, $this->getUser()->getId());

        return $classeurs;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/org/{orgId}/classeurs/search")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @ParamConverter("collectivite", options={"mapping": {"orgId": "id"}})
     * @return Groupe|\Symfony\Component\Form\Form|JsonResponse
     *
     */
    public function searchClasseursAction(Request $request, Collectivite $collectivite)
    {
        if (!$request->request->has('name')) {

            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $filter = new SearchClasseurData($request->request->get('name'));
        $result = $this->get('classeur.manager')->searchClasseurs($collectivite, $this->getUser(), $filter);
        if (false === $result->isSuccess()){
            return new JsonResponse(['errors' => $result->getErrors()], Response::HTTP_BAD_GATEWAY);
        }

        return new JsonResponse($result->getData(), Response::HTTP_OK);
    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("/org/{orgId}/classeurs/list/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listAction($orgId, $sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisibles($orgId, $userId, $sort, $order, $limit, $start);

        return $classeurs;
    }

    /**
     * @param $orgId
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("/org/{orgId}/classeurs/valid/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function validAction($orgId, $sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //@todo MUST refactor!
        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdValidableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursValidable($orgId, $classeursId, $sort, $order, $limit, $start, $user->getId());


        return $classeurs;

    }

    /**
     * @param $orgId
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("/org/{orgId}/classeurs/retract/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRetractAction($orgId, $sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdRetractableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursRetractable($orgId, $classeursId, $sort, $order, $limit, $start, $user->getId());

        return $classeurs;

    }

    /**
     * @param $orgId
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("/org/{orgId}/classeurs/remove/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRemovableAction($orgId, $sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursremovable($orgId, $userId, $sort, $order, $limit, $start);

        return $classeurs;

    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/org/{orgId}/classeurs/{classeurId}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param string $orgId     id collectivite
     * @param string $classeur  id classeur
     *
     * @return Classeur
     */
    public function getByIdAction ($orgId, $classeurId)
    {
        $classeurRepository = $this->getDoctrine()->getManager()->getRepository(Classeur::class);
        $classeur = $classeurRepository->findOneBy(['id' => $classeurId, 'collectivite' => $orgId]);
        if (!$classeur) {
        throw $this->createNotFoundException("Le Classeur n'a pas pu être trouvé");
        }
        $classeur = $classeurRepository
                        ->addClasseurValue($classeur, $this->getUser()->getId());

        foreach ($classeur->getDocuments() as $document) {
            $documentPath = $this->getParameter('upload')['fics'] . $document->getRepourl();
            $fileSize = 0;
            if(file_exists($documentPath)) {
                $fileSize = filesize($documentPath);
            }
            $document->setSize($fileSize);
        }
        return $classeur;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"classeurById"})
     * @Rest\Post("/classeur/new")
     * @param Request $request
     * @return Classeur|\Symfony\Component\Form\Form|JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postAction (Request $request)
    {
        $classeur = new Classeur();

        $form = $this->createForm(ClasseurPostType::class, $classeur);
        $form->submit($request->request->all(), false);
        if ($form->isValid()) {
            $classeur = $form->getData();
            if(!$classeur->getUser() instanceof User) {
                return new JsonResponse(['message' => 'Impossible de mettre à jour le classeur'], Response::HTTP_NOT_MODIFIED);
            }
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('SesileClasseurBundle:Classeur')->setUserVisible($classeur);
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $this->getUser());
            $em->persist($classeur);

            foreach ($request->files as $documents) {
                $em->getRepository('SesileDocumentBundle:Document')->uploadDocuments(
                    $documents,
                    $classeur,
                    $this->getParameter('upload')['fics'],
                    $this->getUser()
                );
            }


            $em->flush();

            $actionMailer = $this->get(ActionMailer::class);
            $actionMailer->sendNotificationClasseur($classeur);

            return $classeur;
        }
        else {
            return new JsonResponse(['message' => 'Impossible de mettre à jour le classeur'], Response::HTTP_NOT_MODIFIED);
        }

    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Patch("/classeur/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     * @return Classeur|\Symfony\Component\Form\Form|JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateAction (Request $request, Classeur $classeur)
    {
        if (empty($classeur)) {
            return new JsonResponse(['message' => 'classeur inexistant'], Response::HTTP_NOT_FOUND);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ||
            $this->getUser()->getId() == $classeur->getUser()->getId()) {

            $etapeClasseurs = new ArrayCollection();
            foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
                $etapeClasseurs->add($etapeClasseur);
            }

            $form = $this->createForm(ClasseurType::class, $classeur);
            $form->submit($request->request->all(), false);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->getRepository('SesileClasseurBundle:Classeur')->setUserVisible($classeur);

                foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
                    $etapeClasseur->setClasseur($classeur);
                    $em->persist($etapeClasseur);
                }
                foreach ($etapeClasseurs as $etapeClasseur) {
                    if ($classeur->getEtapeClasseurs()->contains($etapeClasseur) === false) {
                        $classeur->removeEtapeClasseur($etapeClasseur);
                        $etapeClasseur->setClasseur();
                        $em->remove($etapeClasseur);
                    }
                }
                $this->get('logger')->debug("voici la nouvelle visibilité {visibilite}", array('visibilite' => $classeur->getVisibilite()));
                $em->persist($classeur);
                $em->flush();

                return $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());
            }
            else {
                return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['message' => "Denied Access"], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Put("/action/valid/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     */
    public function validClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $this->getUser());
        $em->flush();

        // Ajout d'une action pour le classeur
        $this->get('classeur.manager')->addClasseurAction($classeur, $this->getUser(), ClasseurManager::ACTION_VALIDATION_CLASSEUR);
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

        $actionMailer = $this->get(ActionMailer::class);
        $actionMailer->sendNotificationClasseur($classeur);

        foreach ($classeur->getDocuments() as $document) {
            $documentPath = $this->getParameter('upload')['fics'] . $document->getRepourl();
            $fileSize = 0;
            if(file_exists($documentPath)) {
                $fileSize = filesize($documentPath);
            }
            $document->setSize($fileSize);
        }

        return $classeur;
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Put("/action/retract/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     */
    public function retractClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileClasseurBundle:Classeur')->retractClasseur($classeur);
        $em->flush();
        // Ajout d'une action pour le classeur
        $this->get('classeur.manager')->addClasseurAction($classeur, $this->getUser(), ClasseurManager::ACTION_RETRACT);
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

        return $classeur;
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Put("/action/refuse/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     * @return Classeur
     */
    public function refuseClasseurAction (Request $request, Classeur $classeur)
    {
        $motif = $request->request->get('motif');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileClasseurBundle:Classeur')->refuseClasseur($classeur, $request->request->get('motif'));
        $em->flush();

        // Ajout d'une action pour le classeur
        $this->get('classeur.manager')->addClasseurAction($classeur, $this->getUser(), ClasseurManager::ACTION_REFUSED, $motif);
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

        $actionMailer = $this->get(ActionMailer::class);
        $actionMailer->sendNotificationClasseur($classeur);

        return $classeur;
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Put("/action/remove/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     */
    public function removeClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileClasseurBundle:Classeur')->removeClasseur($classeur);
        $em->flush();

        // Ajout d'une action pour le classeur
        $this->get('classeur.manager')->addClasseurAction($classeur, $this->getUser(), ClasseurManager::ACTION_REMOVE_CLASSEUR);

        return $classeur;
    }


    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Delete("/action/delete/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return JsonResponse
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN')")
     */
    public function deleteClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
        foreach ($classeur->getDocuments() as $document) {
            $delete = $em->getRepository('SesileDocumentBundle:Document')->removeDocument($this->getParameter('upload')['fics'] . $document->getRepourl());
        }
        $em->remove($classeur);
        $em->flush();

        return new JsonResponse(['message' => "Classeur remove"], Response::HTTP_OK);
    }

    /**
     * Génération du fichier JNLP permettant l exécution de l application de signature
     *
     * @Route("/jnlpsignerfiles/{id}/{role}", name="jnlpSignerFiles")
     * @param Request $request
     * @param $id
     * @param null $role
     * @return Response
     */
    public function jnlpSignerFilesAction($id, $role = null) {
        $ids = explode(",", urldecode($id));
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        // Infos JSON liste des fichiers
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findById($ids);

        // Gestion du role de l utilisateur
        // Dans le cas l utilisateur a plusieurs roles
        if(null !== $role) {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findOneById($role);
            $roleArg = $roleUser->getUserRoles();
        }
        // Dans le cas l utilisateur a un seul role
        else {
            $roleUser = $em->getRepository('SesileUserBundle:UserRole')->findByUser($user);
            if (!empty($roleUser)) {
                $roleArg = $roleUser[0]->getUserRoles();
            } else {
                $roleArg = 'Non renseigné';
            }
        }
        $classeursJSON = array();

        // Generation du token pour les documents
        $token = uniqid();

        // Pour chaque classeurs
        foreach ($classeurs as $classeur) {

            // Recuperation url de retour pour la validation du classeur
            $url_valid_classeur = $this->generateUrl('valider_classeur_jws', array('id' => $classeur->getId(), 'user_id' => $user->getId()), UrlGeneratorInterface::ABSOLUTE_URL);

            $documentsJSON = array();

            foreach ($classeur->getDocuments() as $document) {

                if(!$document->getSigned()) {

                    $document->setToken($token);

                    $typeDocument = $document->getType();

                    // Definition du type de document a transmettre au JWS
                    if(
                        ($typeDocument == "application/xml" || $typeDocument == "text/xml")
                        && $classeur->getType()->getNom() == "Helios"
                    ) {
                        $typeJWS = "xades-pes";
                    } else if($typeDocument == "application/xml") {
                        $typeJWS = "xades";
                    } else if($typeDocument == "application/pdf") {
                        $typeJWS = "pades";
                    } else {
                        $typeJWS = "cades";
                    }

                    $documentsJSON[] = array(
                        'name'          => $document->getName(),
                        'type'          => $typeJWS,
                        'description'   => $classeur->getDescription(),
                        'url_file'      => $this->generateUrl('download_jws_doc', array('name' => $document->getrepourl()), UrlGeneratorInterface::ABSOLUTE_URL),
                        'url_upload'    => $this->generateUrl('upload_document_fron_jws', array('id' => $document->getId()), UrlGeneratorInterface::ABSOLUTE_URL)
                    );
                }

            }

            // On enregistre les modifications du document en bas
            $em->flush();

            // On incrémente les arguments passés
            $classeursJSON[] = array(
                'name' => $classeur->getNom(),
                'url_valid_classeur' => $url_valid_classeur,
                'documents' => $documentsJSON
            );
        }
        $arguments = array();
        $arguments[] = json_encode($classeursJSON);

        // Récupération des infos du user
        $arguments[] = ($user->getPays() === null) ? "Non renseigné" : $user->getPays();
        $arguments[] = ($user->getVille() === null) ? "Non renseignée" : $user->getVille();
        $arguments[] = ($user->getCp() === null) ? "Non renseigné" : $user->getCp();
        $arguments[] = $roleArg;

        // On passse le token
        $arguments[] = $token;


        // Création de la réponse pour envoyer le fichier JNLP générer automatiquement
        $response = new Response();
        // Envoie des bonnes headers pour le JNLP
        $response->headers->set('Content-type', 'application/x-java-jnlp-file');
        $response->headers->set('Content-disposition', 'filename="signer.jnlp"');

        $url_applet = 'http://' . $this->container->getParameter('url_applet') . '/jws/sesile-jws-signer.jar';

        $contentSigner =
            '<?xml version="1.0" encoding="utf-8"?>
                <jnlp spec="1.0+" codebase="' . $this->generateUrl('jnlpSignerFiles', array('id' => urlencode(serialize($ids)), 'role' => $role), UrlGeneratorInterface::ABSOLUTE_URL) . '">
                  <information>
                    <title>SESILE JWS Signer</title>
                    <vendor>SICTIAM</vendor>
                    <homepage href="' . $url_applet . '"/>
                    <description>Application de de signature de documents</description>
                    <description kind="short">Application de signatures</description>
                    <offline-allowed/>
                  </information>
                <security><all-permissions /></security>
                  <resources>
                    <j2se version="1.8" initial-heap-size="128m" max-heap-size="1024m"/>
                    <jar href="' . $url_applet . '"/>
                  </resources>
                  <application-desc >';

        foreach ($arguments as $argument) {
            $contentSigner .= '<argument>' . $argument . '</argument>';
        }

        $contentSigner .= '</application-desc>
        </jnlp>';
        $response->setContent($contentSigner);

        return $response;

    }

    /**
     * Valider_et_signer an existing Classeur entity from JWS.
     *
     * @Rest\Get("/valider_classeur_jws/{id}/{user_id}/{valid}", name="valider_classeur_jws")
     *
     */
    public function valider_classeur_jws(Request $request, $id, $user_id, $valid = -1)
    {

        if($valid == 1) {

            // Connexion BDD
            $em = $this->getDoctrine()->getManager();

            // Récup de l entité classeur et user
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->find($id);
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($user_id);

            // Test si le classeur exite
            if (!$classeur) {
                throw $this->createNotFoundException('Unable to find Classeur entity.');
            }

            // Validation du classeur
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $user);
            $em->flush();

            // Ajout d'une action pour le classeur
            $this->get('classeur.manager')->addClasseurAction($classeur, $this->getUser(), ClasseurManager::ACTION_SIGN, ClasseurManager::ACTION_SIGN_CLASSEUR);

            // Envoie du mail de confirmation
            $this->sendValidationMail($classeur, $user);

            return new JsonResponse(array("classeur_valid" => "1"));
        }
        elseif ($valid == 0) {
            return new JsonResponse(array("classeur_valid" => "0"));
        }
        else {
            return new JsonResponse(array("classeur_valid" => "-1"));
        }
    }
}
