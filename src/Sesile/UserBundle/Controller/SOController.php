<?php

namespace Sesile\UserBundle\Controller;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\UserGroupe;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;
use Sesile\UserBundle\Form\GroupeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;


class SOController extends Controller {

    /**
     * @Route("/services-org", name="servicesorg")
     * @Method("GET")
     * @Template("SesileUserBundle:SO:index.html.twig")
     */
    public function indexAction()
    {
        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $servOrg = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
        return array('servOrgs'=>$servOrg, "menu_color" => "vert");
    }

    /**
 * @Route("/services-org/new", name="new_serviceorg")
 * @Method("GET")
 * @Template("SesileUserBundle:SO:new.html.twig")
 */
    public function newAction()
    {
        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
//        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findBy(
            array("collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1),
            array("Nom" => "ASC")
        );
        $typeClasseurs = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();
        return array('userPacks'=>$userPacks,'users'=>$users,'types'=>$typeClasseurs, "menu_color" => "vert");
    }

    /**
     * @Route("/services-org/new", name="create_serviceorg")
     * @Method("POST")
     * @Template()
     */
    public function createAction(Request $request)
    {

        if(!$request->request->get('nom'))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le service organisationnel doit porter un nom'
            );
            return new RedirectResponse($this->container->get('router')->generate('create_serviceorg'));
        }

        if(!count(json_decode($request->request->get('valeurs'))))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le service organisationnel doit comporter des utilisateurs'
            );
            return new RedirectResponse($this->container->get('router')->generate('create_serviceorg'));
        }
/*
 * On vérifie qu le SO a accès à au moins un type
 * */
        if(is_null($request->request->get('types')))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Le service organisationnel doit être lié à au moins un type de classeur'
            );
            return new RedirectResponse($this->container->get('router')->generate('new_serviceorg'));
        }

        $em = $this->getDoctrine()->getManager();

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));

        $testGroupe = $em->getRepository('SesileUserBundle:Groupe')->findBy(array('collectivite'=>$collectivite,'nom'=>$request->request->get('nom') ));

        /*
         * si on trouve un service organisationnel  avec ce nom alors il est déjà pris : ça dégage
         * */

        if(count($testGroupe))
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Ce nom de service organisationnel existe déjà'
            );
            return new RedirectResponse($this->container->get('router')->generate('new_serviceorg'));
        }



        $tabEtapes = json_decode($request->request->get('valeurs'));

        $serviceOrg = new Groupe();

        $ordreEtape = array();

        foreach($tabEtapes as $k => $etape)
        {
            /*
             * On boucle pour créer les étapes
             * */
            $step  = new EtapeGroupe();

            // initialisation de la variable pour avoir les utilisateurs qui seront dans l etapeGroupe
            $usersTools = array();

            foreach($etape as $elementEtape)
            {
                /*
                 * on boucle pour affecter les users et userPack à l'étape
                 * */
                if($elementEtape->entite == 'groupe')
                {
                    /*
                     * J'ai mis un préfixe user ou userpack dans la value de l'option pour différencié un user d'un userpack car si un user et un userpack on le meme id ça plante
                     *
                     * */
                    list($reste,$idUPack) = explode('-',$elementEtape->id);
                    $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($idUPack);

                    $step->addUserPack($userPack);

                }
                else{
                    list($reste,$idUser) = explode('-',$elementEtape->id);
                    $user = $em->getRepository('SesileUserBundle:User')->findOneById($idUser);
                    $step->addUser($user);
//                    $step->addUsersTool($user);
                    $usersTools[] = $idUser;
                }
            }
            // On met l'ordre des étapes a jour
            $step->setOrdre($k);

            $em->persist($step);
            /*
             * On enrgistre l'étape en base pour avoir son id
             * */
            $em->flush();
            /*
             * On ajoute son id à ordreEtape
             * */
            $ordreEtape[] = $step->getId();
            /*
             * on ajoute l'étape au SO
             * */
            $serviceOrg->addEtapeGroupe($step);


        }
        $ordreEtapeString = '';
        /*
         * On transforme le tab ordreEtape en string(liste d'id Etape séparés par des ,
         * */
        foreach($ordreEtape as $idEtape)
        {
            if($ordreEtapeString != '')
            {
                $ordreEtapeString .= ','.$idEtape;
            }
            else{
                $ordreEtapeString = $idEtape;
            }

        }

        $serviceOrg->setOrdreEtape($ordreEtapeString);
        $serviceOrg->setNom($request->request->get('nom'));


        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));

        $serviceOrg->setCollectivite($collectivite);

        $tabTypeId = $request->request->get('types');

        /*
         * On boucle sur les types sélectionner $tabTypeId est un tableau d'id de type id de type = value checkbox
         * */
        foreach($tabTypeId as $idType)
        {
            $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($idType);
            $serviceOrg->addType($typeClasseur);
        }

        $em->persist($serviceOrg);
        $em->flush();

        /*
         * Une fois le SO inséré en base, on a son id et on l'affecte à chaque etape du SO
         * */
        $tabEtapesAdded = $serviceOrg->getEtapeGroupes();
        foreach($tabEtapesAdded as $etapeGr)
        {
            $etapeGr->setGroupe($serviceOrg);

        }
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le service organisationnel a bien été enregistré'
        );

        return new RedirectResponse($this->container->get('router')->generate('servicesorg'));
    }

    /**
     * @Route("/services-org/edit/{id}", name="edit_serviceorg")
     * @Method("GET")
     * @Template("SesileUserBundle:SO:edit.html.twig")
     */
    public function editAction($id)
    {
        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

        $em = $this->getDoctrine()->getManager();

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
//        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findBy(
            array("collectivite" => $this->get("session")->get("collectivite"), 'enabled' => 1),
            array("Nom" => "ASC")
        );
        $serviceOrg = $em->getRepository('SesileUserBundle:Groupe')->findOneById($id);
        $typeClasseurs = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();

        //Retourne un tableau avec tous les users de la collectivité et un booléen disant si chaque user est dans l'étape ou pas
        return array('userPacks'=>$userPacks,'users'=>$users,'types'=>$typeClasseurs,'service'=>$serviceOrg, "menu_color" => "vert");
    }

    /**
     * @Route("/services-org/modify/{id}", name="modify_serviceorg")
     * @Method("POST")
     * @Template()
     */
    public function modifyAction(Request $request, $id)
    {
        // Verification d usages, repris de createAction
        if(!$request->request->get('nom')) {
            $this->get('session')->getFlashBag()->add('error', 'Le service organisationnel doit porter un nom');
            return new RedirectResponse($this->container->get('router')->generate('edit_serviceorg', array('id' => $id)));
        }

        if(!count(json_decode($request->request->get('valeurs')))) {
            $this->get('session')->getFlashBag()->add('error', 'Les étapes du service organisationnel doit comporter des utilisateurs');
            return new RedirectResponse($this->container->get('router')->generate('edit_serviceorg', array('id' => $id)));
        }

        // On vérifie qu le SO a accès à au moins un type
        if(is_null($request->request->get('types'))) {
            $this->get('session')->getFlashBag()->add('error', 'Le service organisationnel doit être lié à au moins un type de classeur');
            return new RedirectResponse($this->container->get('router')->generate('edit_serviceorg', array('id' => $id)));
        }

        $em = $this->getDoctrine()->getManager();
//        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
//        $testGroupe = $em->getRepository('SesileUserBundle:Groupe')->findBy(array('collectivite'=>$collectivite,'nom'=>$request->request->get('nom') ));
//
//        // si on trouve un service organisationnel  avec ce nom alors il est déjà pris : ça dégage
//        if(count($testGroupe)) {
//            $this->get('session')->getFlashBag()->add('error', 'Ce nom de service organisationnel existe déjà');
//            return new RedirectResponse($this->container->get('router')->generate('new_serviceorg'));
//        }

        // On commence l enregistrement en BDD
        $serviceOrg = $em->getRepository('SesileUserBundle:Groupe')->find($id);

        // On enregistre le nom
        $serviceOrg->setNom($request->request->get('nom'));

        // On enregistre les types
        // Enregistrement des données ManyToMany Groupe <=> TypeClasseur
        $typeGroupe = $request->request->get('types');
        if (null !== $typeGroupe){

            // A lire reference http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-associations.html#removing-associations
            // On supprime les types deja enregistrés
            $serviceOrg->getTypes()->clear();

            // On ajoute les nouveaux types en BDD
            foreach ($typeGroupe as $tg){
                $typeClasseur = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findOneById($tg);

                $serviceOrg->addType($typeClasseur);
                $typeClasseur->addGroupe($serviceOrg);
            }

            $em->persist($typeClasseur);
            $em->persist($serviceOrg);
        }

        // On enregistre les etapes de validation
        $tabEtapes = json_decode($request->request->get('valeurs'));

        //var_dump($request->request->get('valeurs'));

        foreach($tabEtapes as $k => $etape) {
            /*
             * On boucle pour créer les étapes
             * */

            // Si c est une modification d etape
            if ($etape->etape_id != 0) {
                $step = $em->getRepository('SesileUserBundle:EtapeGroupe')->findOneById($etape->etape_id);
                $step->getUsers()->clear();
                $step->getUserPacks()->clear();
//                $step->getUsersTools()->clear();
            }
            // Sinon c est une nouvelle etape
            else {
                $step  = new EtapeGroupe();
                $step->setGroupe($serviceOrg);

                // on ajoute l'étape au SO
                $serviceOrg->addEtapeGroupe($step);
            }
            // On met l'ordre des étapes a jour
            $step->setOrdre($k);

            // initialisation de la variable pour avoir les utilisateurs qui seront dans l etapeGroupe
            $usersTools = array();

            foreach ($etape->etapes as $elementEtape) {
                /*
                 * on boucle pour affecter les users et userPack à l'étape
                 * */

                if ($elementEtape->entite == 'groupe') {
                    /*
                     * J'ai mis un préfixe user ou userpack dans la value de l'option pour différencié un user d'un userpack car si un user et un userpack on le meme id ça plante
                     *
                     * */
                    list($reste, $idUPack) = explode('-', $elementEtape->id);
                    $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($idUPack);

                    $step->addUserPack($userPack);

                    // On ajoute les utilisateurs dans la EtapeGroupe_user_tools
//                    $usersPack = $em->getRepository('SesileUserBundle:User')->findByUserPacks($idUPack);
//                    foreach ($usersPack as $UP) {
//                        $usersTools[] = $UP->getId();
//                    }

                } else {
                    list($reste, $idUser) = explode('-', $elementEtape->id);
                    $user = $em->getRepository('SesileUserBundle:User')->findOneById($idUser);
                    $step->addUser($user);
                    $usersTools[] = $idUser;
                }

            }

            // On dedoublonne les idUsers pour userTool
//            $usersTools = array_unique($usersTools);
//            foreach ($usersTools as $userTools) {
//                $userT = $em->getRepository('SesileUserBundle:User')->findOneById($userTools);
//                $step->addUsersTool($userT);
//            }
            $em->persist($step);
            /*
             * On ajoute son id à ordreEtape
             * */
            $ordreEtape[] = $step->getId();


        }

        /*
         * Suppression des etapes qui ne sont plus utilisées
         */
        $etapes = $serviceOrg->getEtapeGroupes();
        foreach ($etapes as $etape) {
            // recuperation de tous les id de l etape
            $etapeId[] = $etape->getId();
        }
        // On recupere la différence entre les id utilisées et les id du SO
        $etapesDiff = array_diff($etapeId, $ordreEtape);
        foreach ($etapesDiff as $etapeDiffId) {
            $etapeDiff = $em->getRepository('SesileUserBundle:EtapeGroupe')->findOneById($etapeDiffId);

            $em->remove($etapeDiff);
        }

        $ordreEtapeString = '';
        /*
         * On transforme le tab ordreEtape en string(liste d'id Etape séparés par des ,
         * */
        foreach($ordreEtape as $idEtape)
        {
            if($ordreEtapeString != '') {
                $ordreEtapeString .= ','.$idEtape;
            }
            else{
                $ordreEtapeString = $idEtape;
            }
        }

        $serviceOrg->setOrdreEtape($ordreEtapeString);

        // On persiste
        $em->persist($serviceOrg);

        // On met a jour
        $em->flush();
        // petit message pour dire que tout c'est bien passé
        $this->get('session')->getFlashBag()->add('success', 'Le service organisationnel a bien été enregistré');

        // On revient a la page d edition
        return new RedirectResponse($this->container->get('router')->generate('edit_serviceorg', array('id' => $id)));


    }

    /**
     * @Route("/services-org/delete/{id}", name="delete_serviceorg")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction($id) {
        // fonction pour la securite
        if(!$this->securityContext()) { return new RedirectResponse($this->container->get('router')->generate('index')); }

        // On recupere l enregistrement a supprimer
        $em = $this->getDoctrine()->getManager();
        $servOrg = $em->getRepository('SesileUserBundle:Groupe')->findOneById($id);

        $etapeGroupe = $em->getRepository('SesileUserBundle:EtapeGroupe')->findByGroupe($servOrg);
        foreach ($etapeGroupe as $etape) {
            $em->remove($etape);
        }

        // On supprime l enregistrement
        $em->remove($servOrg);
        $em->flush();

        // on redirige avec un petit message
        $this->get('session')->getFlashBag()->add('success', 'Le service organisationnel a bien été supprimé');
        return $this->redirect($this->generateUrl('servicesorg'));

    }

    private function securityContext() {
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return true;
        }
        else {
            return false;
        }
    }

}