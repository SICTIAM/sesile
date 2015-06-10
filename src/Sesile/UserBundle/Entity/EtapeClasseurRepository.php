<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sesile\ClasseurBundle\Entity\Classeur;

/**
 * EtapeClasseurRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtapeClasseurRepository extends EntityRepository
{
    /**
     * Retourne les etapes validantes restantes d un classeur
     *
     * @param Classeur $classeur
     * @return array
     */
    public function findByEtapesAValider(Classeur $classeur, $edit = false) {

        $em = $this->getEntityManager();
        $etapesValidantes = array();
        $etapesGroupes = $em->getRepository('SesileUserBundle:EtapeClasseur')->findBy(
            array('classeur' => $classeur),
            array('ordre' => 'ASC')
        );
//        $tabEtapeClasseur = explode(',',$classeur->getOrdreValidant());
//        $countEtape = $classeur->getOrdreEtape() - 1;
        $countEtape = $classeur->getOrdreEtape();

        if ($countEtape <= 0) $countEtape = 0;
        if ($edit && $classeur->getStatus() != 0) {
            $countEtape++;
        }

        foreach ($etapesGroupes as $etapesGroupe) {
//            var_dump('Ordre classeur : ', $countEtape, 'Ordre etapes : ', $etapesGroupe->getOrdre(), '<br>');
            if($countEtape <= $etapesGroupe->getOrdre()) {
                $etapesValidantes[] = $etapesGroupe;
            }
        }

        return $etapesValidantes;
    }


    /**
     * Retourne la liste des id des utilisateurs dans le circuit
     *
     * @param Classeur $classeur
     * @return array
     */
    public function findAllUsers(Classeur $classeur) {

        $em = $this->getEntityManager();
        $etapesGroupes = $em->getRepository('SesileUserBundle:EtapeClasseur')->findBy(array('classeur' => $classeur));
        $users = array();
        $usersId = array();

        foreach ($etapesGroupes as $etapesGroupe) {
            $users = array_merge($users, $etapesGroupe->getUsers()->toArray());

            $usersPacks = $etapesGroupe->getUserPacks();
            foreach ($usersPacks as $usersPack) {
                $users = array_merge($users, $usersPack->getUsers()->toArray());
            }
        }
        foreach ($users as $user) {
            $usersId[] = $user->getId();
        }
        $usersId = array_unique($usersId);

        return $usersId;

    }

    public function findAllUsersAfterMe(Classeur $classeur) {
        $em = $this->getEntityManager();
        $etapesGroupes = $em->getRepository('SesileUserBundle:EtapeClasseur')->findBy(array('classeur' => $classeur));
        $users = array();
        $usersId = array();
        $etapeCourante = $classeur->getOrdreEtape();

        foreach ($etapesGroupes as $k => $etapesGroupe) {
            if($k < $etapeCourante) continue;
            $users = array_merge($users, $etapesGroupe->getUsers()->toArray());

            $usersPacks = $etapesGroupe->getUserPacks();
            foreach ($usersPacks as $usersPack) {
                $users = array_merge($users, $usersPack->getUsers()->toArray());
            }
        }
        foreach ($users as $user) {
            $usersId[] = $user->getId();
        }
        $usersId = array_unique($usersId);

        return $usersId;
    }

    /**
     * Met a jour les etapes du classeur
     *
     * @param $classeur
     * @param $tabEtapes
     * @return mixed
     */
    public function setEtapesForClasseur ($classeur, $tabEtapes, $ajout = false) {

//        var_dump($tabEtapes);
        $tabEtapes = json_decode($tabEtapes);
        $ordreEtape = array();
        $etapeId = array();
        $em = $this->getEntityManager();
        foreach($tabEtapes as $k => $etape) {

            // Si c est une modification d etape
            if ($etape->etape_id != 0 && !$ajout) {
                $step = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneById($etape->etape_id);

                $step->getUsers()->clear();
                $step->getUserPacks()->clear();

            }
            // Sinon c est une nouvelle etape
            else {
                $step  = new EtapeClasseur();
                $step->setClasseur($classeur);

                // on ajoute l'étape au SO
                $classeur->addEtapeClasseur($step);
            }

            // On met l'ordre des étapes a jour
            if($ajout || $classeur->getStatus() == 0) {
                $step->setOrdre($k);
            }
            else {
                $step->setOrdre($k + $classeur->getOrdreEtape() + 1);
            }

            // On boucle pour créer les étapes
            foreach ($etape->etapes as $elementEtape) {

                /*
                 * on boucle pour affecter les users et userPack à l'étape
                 * */
                if ($elementEtape->entite == 'groupe') {
                    // J'ai mis un préfixe user ou userpack dans la value de l'option pour différencié un user d'un userpack car si un user et un userpack on le meme id ça plante
                    list($reste, $idUPack) = explode('-', $elementEtape->id);
                    $userPack = $em->getRepository('SesileUserBundle:UserPack')->findOneById($idUPack);

                    $step->addUserPack($userPack);
                    // On recupere les utilisateurs pour la visibilite
                    $usersPack = $em->getRepository('SesileUserBundle:User')->findByUserPacks($idUPack);
                    foreach ($usersPack as $UP) {
                        if ($k == 0) { $usersValidant[] = $UP->getId(); }
                    }

                } else {
                    list($reste, $idUser) = explode('-', $elementEtape->id);
                    $user = $em->getRepository('SesileUserBundle:User')->findOneById($idUser);
                    $step->addUser($user);
                    // On recupere les utilisateurs pour la visibilite
                    $usersVisible[] = $idUser;
                }
            }

            $em->persist($step);
            $em->flush();

            // On enregistre la prochaine etape validante
            if (($k == 0 && $classeur->getOrdreValidant() === null) ||
                ($k == 0 && $classeur->getStatus() == 0)) {
                $classeur->setOrdreValidant($step->getId());
            }

            /*
             * On ajoute son id à ordreEtape
             * */
            $ordreEtape[] = $step->getId();

            $em->flush();

//            return $classeur;
        }

        /*
         * Suppression des etapes qui ne sont plus utilisées
         */
        if (!$ajout) {
            $etapes = $classeur->getEtapeClasseurs();
            foreach ($etapes as $etape) {
                // recuperation de tous les id de l etape
                $etapeId[] = $etape->getId();
            }
            // On garde les etapes deja validees
            $etapesValides = explode(',', $classeur->getOrdreValidant());
            $etapeValidesId = array_diff($etapeId, $etapesValides);
//            var_dump($etapesValides, '<br>', $etapeId, '<br>', $etapeValidesId);
            // On recupere la différence entre les id utilisées et les id du SO
            $etapesDiff = array_diff($etapeValidesId, $ordreEtape);
//            var_dump($etapesDiff);
            foreach ($etapesDiff as $etapeDiffId) {
                $etapeDiff = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneById($etapeDiffId);

                $em->remove($etapeDiff);
            }
        }

        $em->flush();
//        die();

        return $classeur;
    }
}
