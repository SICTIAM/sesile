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

    public function getLastValidant(Classeur $classeur)
    {
        $etapeClasseur = $this->createQueryBuilder('ec')
            ->where('ec.classeur = :classeur')
            ->andWhere('ec.etapeValide = true')
            ->setParameter('classeur', $classeur)
            ->orderBy('ec.ordre', 'DESC')
            ->setMaxResults('1')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $etapeClasseur
            ? $userValidant = $etapeClasseur->getUserValidant()
            : $userValidant = null;

        return $userValidant;
    }

    public function getClasseurValidate($user, $type) {
        $classeurs =  $this
            ->createQueryBuilder('ec')
            ->select('COUNT(ec)')
            ->join('ec.classeur', 'c', 'WITH', 'c.type = :type')
            ->where('ec.userValidant = :user')
            ->andWhere('ec.etapeValide = true')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $classeurs;

    }

    /**
     * @param EtapeClasseur $etape
     * @return bool|EtapeClasseur
     */
    public function getPreviousEtape(EtapeClasseur $etape) {
        $classeur = $etape->getClasseur();
        $order = $etape->getOrdre() - 1;

        if ($order < 0) {
            return false;
        }
        else {
            $em = $this->getEntityManager();
            $etapeClasseur = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneBy(
                array(
                    'classeur' => $classeur,
                    'ordre' => $order
                )
            );
            if ($etapeClasseur) {
                return $etapeClasseur;
            }
            else return false;

        }
    }

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
        if ($edit && $classeur->getStatus() != 0 && $classeur->getStatus() != 4) {
            $countEtape++;
        }

        foreach ($etapesGroupes as $etapesGroupe) {

            if($countEtape <= $etapesGroupe->getOrdre()) {
                //var_dump('Ordre classeur : ', $countEtape, 'Ordre etapes : ', $etapesGroupe->getOrdre(), '<br>');
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

        $users = array($classeur->getUser());

        foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
            $users = array_merge($users, $this->getUsersForEtape($etapeClasseur));
        }

        return array_unique($users);
    }

    public function findAllUsersAfterMe(Classeur $classeur) {

        $users = array();

        foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
            if(!$etapeClasseur->getEtapeValide()) {
                $users = array_merge($users, $this->getUsersForEtape($etapeClasseur));
            }
        }

        return array_unique($users);
    }


    public function getUsersForEtape (EtapeClasseur $etapeClasseur) {

        $users = array();

        if ($etapeClasseur->getUsers() !== null) {
            $users = array_merge($users, $etapeClasseur->getUsers()->toArray());
        }

        $usersPacks = $etapeClasseur->getUserPacks();
        foreach ($usersPacks as $usersPack) {
            $users = array_merge($users, $usersPack->getUsers()->toArray());
        }

        return $users;
    }

}
