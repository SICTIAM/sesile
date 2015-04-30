<?php

namespace Sesile\UserBundle\Controller;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\UserGroupe;
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
        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $servOrg = $em->getRepository('SesileUserBundle:Groupe')->findByCollectivite($collectivite);
        return array('servOrgs'=>$servOrg);
    }

    /**
 * @Route("/services-org/new", name="new_serviceorg")
 * @Method("GET")
 * @Template("SesileUserBundle:SO:new.html.twig")
 */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
        $typeClasseurs = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();
        return array('userPacks'=>$userPacks,'users'=>$users,'types'=>$typeClasseurs);
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

        foreach($tabEtapes as $etape)
        {
            /*
             * On boucle pour créer les étapes
             * */
            $step  = new EtapeGroupe();
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
                }
            }
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
        $em = $this->getDoctrine()->getManager();

        $collectivite = $em->getRepository('SesileMainBundle:Collectivite')->findOneById($this->get("session")->get("collectivite"));
        $userPacks = $em->getRepository('SesileUserBundle:UserPack')->findByCollectivite($collectivite);
        $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($collectivite);
        $serviceOrg = $em->getRepository('SesileUserBundle:Groupe')->findOneById($id);
        $typeClasseurs = $em->getRepository('SesileClasseurBundle:TypeClasseur')->findAll();

        //Retourne un tableau avec tous les users de la collectivité et un booléen disant si chaque user est dans l'étape ou pas

        return array('userPacks'=>$userPacks,'users'=>$users,'types'=>$typeClasseurs,'service'=>$serviceOrg);
    }


}