<?php

namespace Sesile\ClasseurBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\ClasseurBundle\Entity\Classeur as Classeur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sesile\ClasseurBundle\Form\ClasseurPostType;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Rest\Route("/apirest/classeur", options = { "expose" = true })
 */
class ClasseurApiController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/list/all")
     */
    public function listAllAction()
    {

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getAllClasseursVisibles($this->getUser()->getId());

        return $classeurs;
    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/list/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursVisibles($userId, $sort, $order, $limit, $start);

        return $classeurs;
    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/valid/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function validAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdValidableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursValidable($classeursId, $sort, $order, $limit, $start, $user->getId());


        return $classeurs;

    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/retract/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRetractAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $classeursId = $em->getRepository('SesileUserBundle:User')->getClasseurIdRetractableForUser($user);
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursRetractable($classeursId, $sort, $order, $limit, $start, $user->getId());

        return $classeurs;

    }

    /**
     * @param null $sort
     * @param null $order
     * @param int $limit
     * @param int $start
     * @param null $userId
     * @return array
     * @Rest\View(serializerGroups={"listClasseur"})
     * @Rest\Get("s/remove/{sort}/{order}/{limit}/{start}/{userId}", requirements={"limit" = "\d+", "start" = "\d+"}, defaults={"sort" = "creation", "order"="DESC", "limit" = 10, "start" = 0})
     */
    public function listRemovableAction($sort = null, $order = null, $limit, $start, $userId = null)
    {
        if (
            $userId === null
            || !($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        ) $userId = $this->getUser()->getId();

        $em = $this->getDoctrine()->getManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->getClasseursremovable($userId, $sort, $order, $limit, $start);

        return $classeurs;

    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Get("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     * @internal param $id
     */
    public function getByIdAction (Classeur $classeur)
    {
        $classeur = $this->getDoctrine()->getManager()->getRepository('SesileClasseurBundle:Classeur')
                        ->addClasseurValue($classeur, $this->getUser()->getId());

        return $classeur;
    }

    /**
     * @Rest\View("statusCode=Response::HTTP_CREATED", serializerGroups={"classeurById"})
     * @Rest\Post("/new")
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
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('SesileClasseurBundle:Classeur')->setUserVisible($classeur);
            $em->persist($classeur);

            foreach ($request->files as $documents) {
                $em->getRepository('SesileDocumentBundle:Document')->uploadDocuments(
                    $documents,
                    $classeur,
                    $this->getParameter('upload')['fics'],
                    $this->getUser()
                );
            }

            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $this->getUser());

            $em->flush();

            $this->sendCreationMail($classeur);

            return $classeur;
        }
        else {
            return new JsonResponse(['message' => 'Impossible de mettre à jour le classeur'], Response::HTTP_NOT_MODIFIED);
        }

    }

    /**
     * @Rest\View()
     * @Rest\Patch("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Classeur $classeur
     * @return Classeur|\Symfony\Component\Form\Form|JsonResponse
     */
    public function updateAction (Request $request, Classeur $classeur)
    {
        if (empty($classeur)) {
            return new JsonResponse(['message' => 'classeur inexistant'], Response::HTTP_NOT_FOUND);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ||
            $this->getUser()->getId() == $classeur->getUser()) {

            $etapeClasseurs = new ArrayCollection();
            foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
                $etapeClasseurs->add($etapeClasseur);
            }

            $form = $this->createForm(ClasseurType::class, $classeur);
            $form->submit($request->request->all(), false);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();

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

                $em->persist($classeur);
                $em->flush();

                return $classeur;
            }
            else {
                return $form;
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

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

        return $classeur;
    }

    /**
     * @Rest\View(serializerGroups={"classeurById"})
     * @Rest\Put("/action/sign/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"}})
     * @param Classeur $classeur
     * @return Classeur
     */
    public function signClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
//        $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $this->getUser());
        $em->flush();
        $this->signClasseur(array($classeur));

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

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

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

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


    private function sendMail($sujet, $to, $body) {
        $message = \Swift_Message::newInstance();
        // Pour l integration de l image du logo dans le mail
        $html = explode("**logo_coll**", $body);
        if($this->get('session')->get('logo') !== null && $this->container->getParameter('upload')['logo_coll'] !== null && !empty($html)) {
            $htmlBody = $html[0] . '<img src="' . $message->embed(\Swift_Image::fromPath($this->container->getParameter('upload')['logo_coll'] . $this->get('session')->get('logo'))) . '" width="75" alt="Sesile">' . $html[1];
        } else {
            $htmlBody = $body;
        }

        // On rajoute les balises manquantes
        $html_brkts_start = "<html><head></head><body>";
        $html_brkts_end = "</body></html>";
        $htmlBodyFinish = $html_brkts_start . $htmlBody . $html_brkts_end;

        // Constitution du mail
        $message->setSubject($sujet)
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($to)
            ->setBody($htmlBodyFinish)
            ->setContentType('text/html');

        // Envoie de l email
        $this->get('mailer')->send($message);
    }

    private function sendCreationMail(Classeur $classeur) {
        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository("SesileMainBundle:Collectivite")->find($this->getUser()->getCollectivite());
        $d_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getUser());

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($collectivite->getTextmailnew());
        $template_html = array(
            'deposant' => $d_user->getPrenom() . " " . $d_user->getNom(),
            'role' => $d_user->getRole(),
            'qualite' => $d_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            "lien" => '<a href="http://'.$this->container->get('router')->getContext()->getHost() . $this->generateUrl('classeur_edit', array('id' => $classeur->getId())) . '">valider le classeur</a>'
        );

        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        foreach($validants as $validant) {
            if ($validant != null) {
                $this->sendMail(
                    "SESILE - Nouveau classeur à valider",
                    $validant->getEmail(),
                    $template->render(
                        array_merge($template_html, array('validant' => $validant->getPrenom() . " " . $validant->getNom()))
                    )
                );
            }
        }

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy !== null && is_array($usersCopy)) {
            foreach ($usersCopy as $userCopy) {
                if($userCopy != null && !in_array($userCopy, $validants)) {
                    $this->sendMail(
                        "SESILE - Nouveau classeur déposé",
                        $userCopy->getEmail(),
                        $template->render(
                            array_merge($template_html, array('validant' => $userCopy->getPrenom() . " " . $userCopy->getNom()))
                        )
                    );
                }
            }
        }
    }



    private function signClasseur ($ids, $role = null) {

        // Connexion BDD
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

        $contentSigner = '<?xml version="1.0" encoding="utf-8"?>
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
}
