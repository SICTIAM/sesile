<?php
namespace Sesile\ApiBundle\Controller;

use Sesile\ClasseurBundle\Entity\Callback;
use Sesile\MainBundle\Domain\Message;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\ClasseursUsers;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sesile\ClasseurBundle\Service\ActionMailer;
use Sesile\ClasseurBundle\Entity\CallbackRepository;


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
     *
     * Status des classeurs : En cours = 1 finalisé = 2, refusé = 0, retiré = 3
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

                    $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            //"validant" => $user ? $user->getId() : 0,
                            "status" => array(0,1,4)
                        ));

                    break;

                case 'a_signer':

                    $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            //"validant" => $user ? $user->getId() : 0,
                            "status" => 1
                        ));

                    /*$classeur = array();

                    foreach ($classeurtosort as $class) {
                        if ($class->isSignable($em)) {
                            $classeur[] = $class;
                        }
                    }*/

                    break;

                case 'finalise':

                    /*$classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "status" => 2
                        ));*/
                    $classeurs = $user->getClasseurs();
                    foreach ($classeurs as $classeurRep) {
                        if($classeurRep->getStatus() == "2") {
                            $classeur[] = array(
                                'id'        => $classeurRep->getId(),
                                'nom'       => $classeurRep->getShortNom()
                            );
                        }
                    }


                    break;

                case 'retire':

                    /*$classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(
                        array(
                            "status" => 3
                        ));*/
                    $classeurs = $user->getClasseurs();
                    foreach ($classeurs as $classeurRep) {
                        if($classeurRep->getStatus() == "3") {
                            $classeur[] = array(
                                'id'        => $classeurRep->getId(),
                                'nom'       => $classeurRep->getShortNom()
                            );
                        }
                    }

                    break;

                case 'prive':

                    break;
                default:
                    break;


            }

            if ($request->query->get('filtre') == "a_valider" || $request->query->get('filtre') == "a_signer") {
                $users[] = $this->getUser();

                foreach ($classeurs as $classeurRep) {
                    $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeurRep);
                    if(array_intersect($users, $validants))
                    {
                        $classeur[] = array(
                            'id'        => $classeurRep->getId(),
                            'nom'       => $classeurRep->getShortNom()
                        );
                    }
                }
            }



        } else {
            $classeurs = array();
            foreach($user->getClasseurs() as $classeur)
            {
                $classeurs[] = array('id'=>$classeur->getId(),'Nom'=>$classeur->getNom());
            }
            //$classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseursVisibles($user->getId());
            return $classeurs;
        }


        return $classeur;


    }

    /**
     * Cette méthode permet de récupérer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     *
     *
     * Status des classeurs : En cours = 1 finalisé = 2, refusé = 0, retiré = 3
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
        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        if (!in_array($classeur, $user->getClasseurs()->toArray())) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès à ce classeur");
        }

        return $this->classeurToArray($classeur);


    }


    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * Cette méthode permet de déposer un classeur
     *
     * Si l'utilisateur courant n'as pas accès au classeur, un 403 not allowed sera renvoyé
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de créer un nouveau classeur",
     *  parameters={
     *          {"name"="name", "dataType"="string", "format"="Maximum de 250 caractères", "required"=true, "description"="Nom du classeur"},
     *          {"name"="desc", "dataType"="string", "format"="Maximum de 250 caractères", "required"=false, "description"="Description du classeur"},
     *          {"name"="validation", "dataType"="string", "format"="dd/mm/aaaa", "required"=true, "description"="Date limite de validation classeur"},
     *          {"name"="type", "dataType"="integer", "format"="", "required"=true, "description"="id du type du classeur"},
     *          {"name"="groupe", "dataType"="integer", "format"="", "required"=true, "description"="groupe de validation du classeur"},
     *          {"name"="visibilite", "dataType"="integer", "format"="0 si Privé, 1 Public, 3 pour le groupe fonctionnel, (2 est indisponible pour le dépôt d'un classeur)", "required"=true, "description"="Visibilité du classeur"},
     *          {"name"="email", "dataType"="string", "format"="Email valide", "required"=false, "description"="email du déposant"},
     *          {"name"="callback", "dataType"="string", "format"="URL valide", "required"=false, "description"="URL de rappel, sur les événements classeur (signé, retiré, supprimé)"},
     *          {"name"="siren", "dataType"="string", "format"="string", "required"=false, "description"="siren collectivité, si non renseigné la premiere collectivité de l'utilisateur sera utilisé"},
     *  }
     * )
     *
     * @var Request $request
     * @return JsonResponse
     * @Route("/")
     * @Rest\View()
     * @Method("post")
     *
     * @todo newActions needs the collectivite in order to set the correct collectivity id into the classeur
     *
     */
    public function newAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $email = $request->request->get('email');
        if(is_null($email))
        {
            $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
            $userAPI = null;
        }
        else{
            //@todo findOneByUsername? plutôto findOneByEmail?
            $user = $em->getRepository('SesileUserBundle:User')->findOneByUsername($email);
            $userAPI = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        }
        if ($request->request->has('siren')) {
            $collectivity = $this->get('collectivite.manager')
                ->getCollectiviteBySiren($request->request->get('siren'))
                ->getData();
        } else {
            $collectivity = $user->getFirstCollectivity();
        }

        $serviceOrgs = $em->getRepository('SesileUserBundle:EtapeGroupe')->findByUsers($user->getId());


        if(!count($serviceOrgs)) {
            $this->get('session')->getFlashBag()->add('notice', 'Vous ne faites parti d\'aucun circuit de validation.');
            return $this->redirect($this->generateUrl('classeur'));
        }

        foreach($serviceOrgs as $serviceOrg) {
            $groupetto = $em->getRepository('SesileUserBundle:Groupe')->findOneById($serviceOrg);
            //@todo ceci peut donner un 500 si $groupetto == null
            $groupes[] = $groupetto->getId();
        }

        $groupes_unique = array_unique($groupes);

        if(!in_array($request->request->get('groupe'),$groupes_unique))
        {
            $view = $this->view(array('code' => '400', 'message' => 'Utilisateur introuvable pour ce groupe', "parametres_recus" => $request->request), 400);
            return $this->handleView($view);
        }

        $tip = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($request->request->get('type'));
        $tabgroups_types = $tip->getGroupes();
        $tabidG = array();
        foreach ($tabgroups_types as $objGroupe) {
            $tabidG[] = $objGroupe->getId();
        }
        if (!in_array($request->request->get('groupe'), $tabidG)) {
            $view = $this->view(array('code' => '400', 'message' => 'Ce groupe n\'a pas accès au type de classeur renseigné ', "parametres_recus" => $request->request), 400);
            return $this->handleView($view);
        }



        $name = $request->request->get('name');



        $validation = $request->request->get('validation');

        $type= $request->request->get('type');





        if (empty($name)|| empty($validation)||empty($type)||empty($request->request->get('groupe'))) {
            $view = $this->view(array('code' => '400', 'message' => 'Paramètres manquants', "parametres_recus"=> $request->request ), 400);
            return $this->handleView($view);
        }

        $groupe = $em->getRepository('SesileUserBundle:Groupe')->findOneById($request->request->get('groupe'));


        $em = $this->getDoctrine()->getManager();

        $classeur = new Classeur();
        $classeur->setNom($request->request->get('name'));
        $classeur->setDescription($request->request->get('desc'));
        list($d, $m, $a) = explode("/", $validation);
        $valid = new \DateTime($m . "/" . $d . "/" . $a);
        $classeur->setValidation($valid);


        $type = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($type);
        $classeur->setType($type);


        $classeur->setUser($user);
        // TODO a modifier par la bonne etape ?
        $classeur->setEtapeDeposante($user->getId());

        $classeur->setVisibilite($request->request->get('visibilite'));
        $classeur->setCollectivite($collectivity);
        $classeur->setCircuitId($groupe);
        $em->persist($classeur);
        $em->flush();

        $tabEtapes = array();

        $etapesGroupe = $em->getRepository('SesileUserBundle:EtapeGroupe')->findBy(
            array('groupe' => $request->request->get('groupe')),
            array('ordre' => 'ASC')
        );

        $enable = false;
        $etapeDeposante = 0;
        foreach ($etapesGroupe as $etapeGroupe) {

            $usersFromEtapes = $etapeGroupe->getUsers();
            foreach ($usersFromEtapes as $user) {

                if($user->getId() == $user->getId() && $etapeDeposante == 0) {
                    $etapeDeposante = $etapeGroupe->getId();
                    $enable = true;
                }
            }

            $userPacksEtapes = $etapeGroupe->getUserPacks();
            foreach ($userPacksEtapes as $userPacksEtape) {
                $usersFromUP = $userPacksEtape->getUsers();

                foreach ($usersFromUP as $userFromUP) {

                    if($userFromUP->getId() == $user->getId() && $etapeDeposante == 0) {
                        $etapeDeposante = $etapeGroupe->getId();
                        $enable = true;
                    }
                }
            }

            if($enable && $etapeDeposante != $etapeGroupe->getId()) {
                $tabEtapes[] = $etapeGroupe;
            }

        }

        foreach($tabEtapes as $k=>$etape)
        {
            $step = new EtapeClasseur();
            $step->setClasseur($classeur);
            foreach($etape->getUserPacks() as $uPack)
            {
                $step->addUserPack($uPack);
            }

            foreach($etape->getUsers() as $userStep)
            {
                $step->addUser($userStep);
            }

            $step->setOrdre($k);
            if ($k == 0) {
                $step->setEtapeValidante(1);
            }
            $em->persist($step);
            $classeur->addEtapeClasseur($step);
            $em->flush();
            if (($k == 0 && $classeur->getOrdreValidant() === null) ||
                ($k == 0 && $classeur->getStatus() == 0)
            ) {
                $classeur->setOrdreValidant($step->getId());
            }
        }

        $em->getRepository('SesileClasseurBundle:Classeur')->setUserVisible($classeur);

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($user);
        $action->setAction("Dépot du classeur");
        $em->persist($action);
        $em->flush();

        $actionMailer = $this->get(ActionMailer::class);
        $actionMailer->sendNotificationClasseur($classeur);

        if (!empty($request->request->get('callback')) && trim($request->request->get('callback')) != "") {
            try {
                $callback = new Callback();
                $callback->setClasseurId($classeur->getId());
                $callback->setUrl($request->request->get('callback'));
                $em->persist($callback);
                $em->flush();
            } catch (\Exception $e) {
                $this->get('logger')->error(sprintf('[SesileApiBundleClasseur]/new Error add callback url: %s, for classeur %s', $e->getMessage(), $classeur->getId()));
            }
        } else {
            $this->get('logger')->debug(sprintf('[SesileApiBundleClasseur]/new Callback url not specified, for classeur %s', $classeur->getId()));
        }
        return new JsonResponse(
            [
                "id" => $classeur->getId(),
                "url" => $actionMailer->buildAbsoluteUrl($collectivity) . 'classeur/' . $classeur->getId(),
                "message" => "Le classeur a bien été déposé",
            ], JsonResponse::HTTP_OK);
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
     *  description="Permet d'editer un classeur. Méthode obsolète",
     *  parameters={
     *          {"name"="name", "dataType"="string", "format"="Maximum de 250 caractères", "required"=true, "description"="Nom du classeur"},
     *          {"name"="desc", "dataType"="string", "format"="Maximum de 250 caractères", "required"=false, "description"="Description du classeur"},
     *          {"name"="validation", "dataType"="string", "format"="dd/mm/aaaa", "required"=true, "description"="Date limite de validation classeur"},
     *          {"name"="circuit", "dataType"="string", "format"="userid,userid,userid...   Par exemple : 1,2,3", "required"=true, "description"="Circuit de validation du classeur"},
     *          {"name"="visibilite", "dataType"="integer", "format"="0 si Public, -1 si privé", "required"=true, "description"="Visibilité du classeur"}
     *  }
     * )
     *
     * @todo REFACTOR
     **/
    public function updateAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        if ($request->request->get('name') == '' || $request->request->get('validation') == '' || $request->request->get('circuit') == '') {
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
        $classeur->setUser($user);

        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur);
        $action->setUser($user);

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
        return $em->getRepository("SesileClasseurBundle:Classeur")->findOneById($id);
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
     *      {"name"="id", "dataType"="integer", "description"="id du classeur"}
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
        $classeur[0]->setOrdreValidant('');
        $classeur[0]->setStatus(3);
        $em->persist($classeur[0]);
        $em->flush();

        $action = new Action();
        $action->setClasseur($classeur[0]);
        $action->setUser($user);
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
     *  description="Permet d'ajouter des documents à un classeur",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "description"="id du classeur"}
     *  }
     *
     * )
     */
    public function newDocumentAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));


        $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);


        if (!in_array($classeur, $user->getClasseurs()->toArray())) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès à ce classeur");
        }

        $elclasseur = $classeur;

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
            $document->setClasseur($elclasseur);
            $em->persist($document);


            $action = new Action();
            $action->setClasseur($elclasseur);
            $action->setUser($user);
            $action->setAction("Ajout du document au classeur " . $elclasseur->getNom());
            $em->persist($action);


            $em->flush();
            $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Ajout du document au classeur " . $elclasseur->getNom(), null);

            $added[] = $document;

        }


        $res = array();

        foreach ($added as $doc) {
            $res = $this->docToArray($doc);
        }
        return $res;



    }

    /**
     * Cette méthode permet de récupérer la liste des types de classeurs
     *
     *
     *
     * @var Request $request
     * @return array
     * @Route("/types/")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer la liste des types de classeurs"
     *
     * )
     */
    public function getTypesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();
        $arrayTypes = array();
        foreach ($types as $type) {
            $arrayTypes[] = array('id' => $type->getId(), 'nom' => $type->getNom());
        }
        return $arrayTypes;
    }

       /*                MAILS DE NOTIFICATION                      */

    /*private function sendMail($sujet, $to, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom($this->container->getParameter('email_sender_address'))
            ->setTo($to)
            ->setBody($body, "text/html");
        $this->get('mailer')->send($message);
    }*/

    private function sendMail($sujet, $to, $body)
    {
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


    private function sendCreationMail($classeur) {
        $em = $this->getDoctrine()->getManager();
        //@todo refactor $this->get("session")->get("collectivite") maybe use: $classeur->getCollectivite();
//        $coll = $em->getRepository("SesileMainBundle:Collectivite")->find($this->get("session")->get("collectivite"));
        $coll = $classeur->getCollectivite();
        //@todo $classeur->getPrevValidant() n'existe plus!! changer avec $classeur->getUser()?
        //$classeur->getPrevValidant() : l'id du précédent validant dans le circuit. L'id du déposant si on revient au premier
//        $c_user = $em->getRepository("SesileUserBundle:User")->find($classeur->getPrevValidant());
        $c_user = $classeur->getUser();
        //Twig_Loader_String is depricated
//        $env = new \Twig_Environment(new \Twig_Loader_String());
        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($coll->getTextmailnew());
        $template_html = [
            'deposant' => $c_user->getPrenom() . " " . $c_user->getNom(),
            'role' => $c_user->getRole(),
            'qualite' => $c_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            'lien' => '<a href="' . $this->container->get('router')->generate('classeur_edit', ['id' => $classeur->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '">valider le classeur</a>'
        ];


        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        foreach($validants as $validant) {
            if ($validant != null) {
                $template_html['validant'] = $validant->getPrenom()." ".$validant->getNom();
                $body = $template->render($template_html);
                $this->sendMail("SESILE - Nouveau classeur à valider", $validant->getEmail(), $body);
            }
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

    private function recursivesortHierarchie($hierarchie, $curr)
    {
        static $recurs = 0;
        foreach ($hierarchie as $k => $groupeUser) {
            if ($groupeUser->getUser()->getId() == $curr) {
                if ($recurs > 0) {
                    $this->ordre .= $groupeUser->getUser()->getId() . ",";
                }

                if ($curr != 0) {
                    $recurs++;
                    $this->recursivesortHierarchie($hierarchie, $groupeUser->getParent());
                }

            }
        }
        $this->ordre = rtrim($this->ordre, ",");
        return $this->ordre;
    }


    private function classeurToArray($classeur)
    {
        /* return array('id' => $classeur->getId(),
             'nom' => $classeur->getNom(),
             'description' => $classeur->getDescription(),
             'creation' => $classeur->getCreation(),
             'validation' => $classeur->getValidation(),
             'type' => $classeur->getType()->getId(),
             'status' => $classeur->getStatus(),
             'user' => $classeur->getUser(),
             'validant' => $classeur->getValidant(),
             'visibilite' => $classeur->getVisibilite(),
             'circuit' => $classeur->getCircuit(),
             'documents' => $classeur->getDocuments(),
             'actions' => $classeur->getActions());
 */
        $tabActions = $classeur->getActions();
        $cleanTabAction = array();
        foreach ($tabActions as $action) {
            $cleanTabAction[] = $this->actionToArray($action);
        }

        $tabDocs = $classeur->getDocuments();
        $cleanTabDocs = array();
        if (count($tabDocs) > 0 ) {
                foreach ($tabDocs as $doc) {
                $cleanTabDocs[] = $this->docToArray($doc);
            }
        }
        $em = $this->getDoctrine()->getManager();
        $validants = $em->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        $tabValidant = array();
        foreach($validants as $validant)
        {
            $tabValidant[] = array('id'=>$validant->getId(),'nom'=>$validant->getPrenom().' '.$validant->getNom());
        }

        return array('id' => $classeur->getId(),
            'nom' => $classeur->getNom(), 'description' => $classeur->getDescription(),
            'creation' => $classeur->getCreation(),
            'validation' => $classeur->getValidation(), 'type' => $classeur->getType()->getId(),
            'validant' => $tabValidant,
            'visibilite' => $classeur->getVisibilite(),
            'circuit' => $classeur->getCircuit(),
            'status' => $classeur->getStatus(),
            'documents' => $cleanTabDocs,
            'actions' => $cleanTabAction);
    }

    private function actionToArray($action)
    {
        return array(
            'id' => $action->getId(),
            'username' => $action->getUsername(),
            'date' => $action->getDate(),
            'action' => $action->getAction(),
            'observation' => $action->getObservation()
        );
    }

    private function docToArray($doc)
    {
        $tabHisto = $doc->getHistories();
        $cleanTabHisto = array();
        foreach ($tabHisto as $histo) {
            $cleanTabHisto[] = $this->histoToArray($histo);
        }
        return array('id' => $doc->getId(),
            'name' => $doc->getName(),
            'repourl' => $doc->getrepourl(),
            'type' => $doc->getType(),
//            'signed' => false,
            'signed' => $doc->getSigned(),
            'histories' => $cleanTabHisto);
    }

    private function histoToArray($histo)
    {
        return array('id' => $histo->getId(),
            'date' => $histo->getDate(),
            'comment' => $histo->getComment());
    }

    /**
     * Fonction pour determiner la visibilite et enregister dans Classeur_visible
     *
     * @param $visibilite
     * @param $users
     * @param null $requestUserGroupe
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function classeur_visible($visibilite, $users, $requestUserGroupe = false, $collectivity) {
        $em = $this->getDoctrine()->getManager();
        switch ($visibilite) {
            // Privé soit le circuit
            case 0:
                return $em->getRepository('SesileUserBundle:User')->findById(array_unique($users));
                break;
            // Public
            case 1:
                return $em->getRepository('SesileUserBundle:User')->findUsersByCollectivitySiren($collectivity->getSiren());
                break;
            // Privé à partir de moi
            case 2:
                return '2';
                break;
            // Pour le service organisationnel (et le circuit)
            case 3:
                if ($requestUserGroupe) {

                    $userGroupe = array();
                    // Service Organistionnel
                    $servicesOrg = $em->getRepository('SesileUserBundle:Groupe')->findById($requestUserGroupe);
                    foreach ($servicesOrg as $serviceOrg) {

                        // Etapes du SO
                        $etapesGroupe = $serviceOrg->getEtapeGroupes();
                        foreach ($etapesGroupe as $etapeGroupe) {

                            // UserPack des etapes
                            $usersPacks = $etapeGroupe->getUserPacks();
                            foreach ($usersPacks as $usersPack) {

                                // Users des UserPack
                                $usersFromUserPack = $usersPack->getUsers();
                                foreach ($usersFromUserPack as $userFromUserPack) {
                                    $userGroupe[] = $userFromUserPack->getId();
                                }
                            }

                            // Liste des utilisateurs directement ajouté
                            $usersFromEtapes = $etapeGroupe->getUsers();
                            foreach ($usersFromEtapes as $usersFromEtape) {
                                $userGroupe[] = $usersFromEtape->getId();
                            }
                        }
                    }
                    $usersAll = array_unique(array_merge($userGroupe, $users));
                    return $em->getRepository('SesileUserBundle:User')->findById($usersAll);
                } else {
                    return $this->redirect($this->generateUrl('classeur_create'));
                }
                break;
            // Par défaut on fait quoi ?
            default:
                return array();
                break;
        }
    }
}