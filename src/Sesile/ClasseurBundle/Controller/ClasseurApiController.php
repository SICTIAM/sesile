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
     * @Rest\Delete("/{id}")
     * @ParamConverter("Classeur", options={"mapping": {"id": "id"})
     * @param Classeur $classeur
     */
    /*public function removeAction (Classeur $classeur)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($classeur);
        $em->flush();
    }*/

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
     * @return Classeur|\Symfony\Component\Form\Form|JsonResponse
     */
    public function validClasseurAction (Classeur $classeur) {

        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SesileClasseurBundle:Classeur')->validerClasseur($classeur, $this->getUser());
        $em->flush();

        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->addClasseurValue($classeur, $this->getUser()->getId());

        return $classeur;
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
}
