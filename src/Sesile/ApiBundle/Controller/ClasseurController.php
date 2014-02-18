<?php
namespace Sesile\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\ClasseursUsers;


/**
 * Class UserController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/classeur")
 */
class ClasseurController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer la liste des classeurs
     *
     * Si aucun paramètre renvoie tous les classeurs visibles par l'utilisateur courant,
     * Sinon renvoie une partie de cette liste en fonction du filtre
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("get")
     *
     * @QueryParam(name="filtre", requirements="(a_valider|a_signer|finalise|termine|prive)", strict=false, nullable=true, description="Filtre optionnel ( exemple: ?filtre=a_valider )")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer la liste des classeurs"
     * )
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        $classeur = array();

        if ($request->query->has('filtre')) {

            switch ($request->query->get('filtre')) {

                case 'a_valider':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "validant" => $user ? $user->getId() : 0,
                            "status" => 1
                        ));

                    break;

                case 'a_signer':

                    $classeurtosort = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "validant" => $user ? $user->getId() : 0,
                            "status" => 1
                        ));

                    $classeur = array();

                    foreach ($classeurtosort as $class) {
                        if ($class->isSignable($em)) {
                            $classeur[] = $class;
                        }
                    }

                    break;

                case 'finalise':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "status" => 2
                        ));

                    break;

                case 'retire':

                    $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "status" => 3
                        ));

                    break;

                case 'prive':

                    break;
                default:
                    break;


            }


        } else {
            $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursVisibles($user->getId());
        }


        return $classeur;


    }

    /**
     * Cette méthode permet de récupérer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer un classeur",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Id du classeur à obtenir"}
     *  }
     * )
     */
    public function getAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($id, $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès à ce classeur");
        }
        return $classeur[0];


    }


    /**
     * Cette méthode permet de récupérer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de créer un nouveau classeur",
     *  parameters={
     *          {"name"="name", "dataType"="string", "required"=true, "description"="Nom du classeur"},
     *          {"name"="desc", "dataType"="string", "required"=false, "description"="Description du classeur"},
     *          {"name"="validation", "dataType"="string", "format"="dd/mm/aaaa", "required"=true, "description"="Date limite de validation classeur"},
     *          {"name"="type", "dataType"="string", "format"="Choix possibles : 'Classique' (Divers), 'Hélios', 'Acte' (Acte Budgétaire), 'Marchés', 'Convocation', 'Courrier AR', 'Document Urba'", "required"=true, "description"="Type du classeur"},
     *          {"name"="circuit", "dataType"="string", "format"="userid,userid,userid...   Par exemple : 1,2,3", "required"=true, "description"="Circuit de validation du classeur"},
     *          {"name"="visibilite", "dataType"="integer", "format"="0 si Public, -1 si privé", "required"=true, "description"="Visibilité du classeur"}
     *
     *
     *
     *  }
     * )
     *
     * @var Request $request
     * @return array
     * @Route("/")
     * @Rest\View()
     * @Method("post")
     *
     *
     *
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        if (empty($request->request->get('name')) || empty($request->request->get('validation')) || empty($request->request->get('type')) || empty($request->request->get('circuit'))) {
            $view = $this->view(array('code' => '400', 'message' => 'Paramètres manquants'), 400);
            return $this->handleView($view);
        }


        $classeur = new Classeur();
        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));
        list($d, $m, $a) = explode("/", $request->request->get('validation'));
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);
        $classeur->setType($request->request->get('type'));
        $circuit = $request->request->get('circuit');
        $classeur->setCircuit($circuit);
        $classeur->setUser($user->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));
        $em->persist($classeur);


        $em->flush();

        // enregistrer les users du circuit
        $users = explode(',', $circuit);

        for ($i = 0; $i < count($users); $i++) {
            $classeurUser = new ClasseursUsers();
            $classeurUser->setClasseur($classeur);

            $userObj = $em->getRepository("SesileUserBundle:User")->findOneById($users[$i]);
            $classeurUser->setUser($userObj);
            $classeurUser->setOrdre($i + 1);
            $em->persist($classeurUser);
        }
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Dépot du classeur");
        $em->persist($action);
        $em->flush();

        // $respDocument = $this->forward( 'sesile.document:createAction', array('request' => $request));

        // envoi d'un mail au premier validant
        $this->sendCreationMail($classeur);

        // TODO envoi du mail au déposant et aux autres personnes du circuit ?


        return $em->getRepository("SesileClasseurBundle:Classeur")->findOneById($classeur->getId());;

    }


    /**
     * Cette méthode permet d'editer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("put")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'editer un classeur",
     *  parameters={
     *          {"name"="name", "dataType"="string", "required"=true, "description"="Nom du classeur"},
     *          {"name"="desc", "dataType"="string", "required"=false, "description"="Description du classeur"},
     *          {"name"="validation", "dataType"="string", "format"="dd/mm/aaaa", "required"=true, "description"="Date limite de validation classeur"},
     *          {"name"="circuit", "dataType"="string", "format"="userid,userid,userid...   Par exemple : 1,2,3", "required"=true, "description"="Circuit de validation du classeur"},
     *          {"name"="visibilite", "dataType"="integer", "format"="0 si Public, -1 si privé", "required"=true, "description"="Visibilité du classeur"}
     *
     *
     *
     *  }
     * )
     */
    public function updateAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        if (empty($request->request->get('name')) || empty($request->request->get('validation')) || empty($request->request->get('circuit'))) {
            $view = $this->view(array('code' => '400', 'message' => 'Paramètres manquants'), 400);
            return $this->handleView($view);
        }


        $classeurs = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($id, $user->getId());


        if (empty($classeurs[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès à ce classeur");
        }


        $classeur = $classeurs[0];


        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));

        list($d, $m, $a) = explode("/", $request->request->get('validation'));
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);
        $circuit = $request->request->get('circuit');
        $classeur->setCircuit($circuit);
        $classeur->setUser($this->getUser()->getId());

        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($this->getUser());
        $action->setAction("Modification du classeur");
        $em->persist($action);
        $em->flush();

        /**
         * TODO modifier le fonctionnement : on doit updater les users par la collection et non par suppression / rajout (un peu de propreté qd même!!!)
         */
        // gestion du circuit
        $users = explode(',', $circuit);
        $classeurUserObj = $em->getRepository("SesileClasseurBundle:ClasseursUsers");
        $classeurUserObj->deleteClasseurUser($classeur, $circuit);
        $em->flush();

        for ($i = 0; $i < count($users); $i++) {
            $userObj = $em->getRepository("SesileUserBundle:User")->findOneById($users[$i]);
            $classeurUser = $classeurUserObj->findOneBy(array("user" => $userObj, "classeur" => $classeur));

            $exist = true;

            if (empty($classeurUser)) {
                $exist = false;
                $classeurUser = new ClasseursUsers();
                $classeurUser->setClasseur($classeur);
                $classeurUser->setUser($userObj);
            }

            $classeurUser->setOrdre($i + 1);
            if (!$exist) {
                $em->persist($classeurUser);
            }

        }

        $em->flush();


        return $em->getRepository("SesileClasseurBundle:Classeur")->findOneById($id);;


        return array();

    }


    /**
     * Cette méthode permet de retirer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("delete")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de retirer un classeur",
     *  requirements={
     *
     *  }
     * )
     */
    public function deleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($id, $user->getId());
        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès à ce classeur");
        }
        $classeur[0]->supprimer();
        $em->persist($classeur[0]);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur[0]);
        $action->setUser($this->getUser());
        $action->setAction("Classeur retiré");
        $em->persist($action);
        $em->flush();

        return array('code' => '200', 'message' => 'Classeur retiré');


    }


    /**
     * Cette méthode permet de créer un ou plusieurs document et de les associer à un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     * Vous devez transmettre vos fichiers en attachment à la manière d'un formulaire web (accessible en php par $_FILES)
     *
     *
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/newDocuments")
     * @Rest\View()
     * @Method("post")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'ajouter des documents à un classeur"
     *
     * )
     */
    public function newDocumentAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));


        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($id, $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès au classeur " . $id);
        }

        $added = array();

        // obtenir une instance de UploadedFile identifiée par file

        foreach ($request->files->all() as $file) {

            $name = $file->getClientOriginalName();
            $movedfile = $file->move($this->get('kernel')->getRootDir() . '/../web/uploads/docs/', uniqid() . '.' . $file->getExtension());


            $document = new Document();
            $document->setName($name);
            $document->setRepourl($movedfile->getBasename()); //Temporairement associé au nom du fichier en attendant les repository git
            $document->setType($movedfile->getMimeType());
            $document->setSigned(false);
            $document->setClasseur($classeur[0]);
            $em->persist($document);


            $action = new Action();
            $action->setClasseur($classeur[0]);
            $action->setUser($user);
            $action->setAction("Modification du document " . $document->getName());
            $em->persist($action);


            $em->flush();
            $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur " . $classeur[0]->getNom(), null);

            $added[] = $document;

        }


        return $added;

    }

    /*                MAILS DE NOTIFICATION                      */

    private function sendMail($sujet, $to, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom('sesile@sictiam.fr')
            ->setTo($to)
            ->setBody($body, "text/html");
        $this->get('mailer')->send($message);
    }

    private function sendValidationMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:valide.html.twig',
            array(
                'validant' => $this->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendCreationMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:nouveau.html.twig',
            array(
                'deposant' => $classeur->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Nouveau classeur à valider", $validant_obj->getEmail(), $body);
        }
    }

    private function sendRefusMail($classeur)
    {
        $body = $this->renderView('SesileClasseurBundle:Mail:refuse.html.twig',
            array(
                'deposant' => $classeur->getUser(),
                'titre_classeur' => $classeur->getNom(),
                'date_limite' => $classeur->getValidation(),
                "lien" => $this->generateUrl('classeur_edit', array('id' => $classeur->getId()))
            )
        );

        $em = $this->getDoctrine()->getManager();
        $validant_obj = $em->getRepository('SesileUserBundle:User')->find($classeur->getValidant());

        if ($validant_obj != null) {
            $this->sendMail("SESILE - Classeur refusé", $validant_obj->getEmail(), $body);
        }
    }

}