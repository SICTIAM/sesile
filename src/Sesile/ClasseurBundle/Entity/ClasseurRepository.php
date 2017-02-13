<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\BrowserKit\Request;

/**
 * ClasseurRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClasseurRepository extends EntityRepository {

    /*
     * Return number of classeurs visible for user
     *
     * @param integer user id
     * @return integer
     */
    public function countClasseursVisiblesForDTablesV3($userid) {
        return $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userid)
            ->getQuery()
//            ->getSingleScalarResult()
            ->getResult()
        ;
    }

    /*
     * Return number of classeurs visible for super admin
     *
     * @param integer user id
     * @return integer
     */
    public function countClasseursVisiblesForDTablesV3SuperAdmin() {
        return $this
            ->createQueryBuilder('c')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
     * Get current classeurs visible for Data Tables
     *
     * @param integer user id
     * @param array get values of Data Tables
     */
    public function getClasseursVisiblesForDTablesV3($userid, $get) {

        $qb = $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userid)
            ->join('c.type', 't')
            ->addSelect('t')
        ;

//        var_dump($get->get('order'));
        // Pour changer l ordre du tableau
        $colonnes = array('nom', 'creation', 'validation', 'intervenants', 'type', 'status');

        if($get->get('order') !== null) {
            // Condition spéciale pour trier par type par ordre alphabétique
            if ($colonnes[$get->get('order')[0]["column"]] == "type") {
                $order = 't.nom';
            } else {
                $order = 'c.' . $colonnes[$get->get('order')[0]["column"]];
            }
//        $order == 'c.type' ? $order = 't.nom' : $order;
//            var_dump('Column', $get->get('order')[0]["column"], "GET order : ", $get->get('order')[0]["dir"]);
            $qb->orderBy($order, $get->get('order')[0]["dir"]);
        }

        /*if(isset($get['order'])) {
            $order = 'c.'.strtolower($get["colonnes"][$get["order"][0]["column"]]);
            $order == 'c.type' ? $order = 't.nom' : $order;
            $qb->orderBy($order, $get['order'][0]["dir"]);
        }*/

        // Pour la recherche dans le tableau
        if (isset($get->get('search')["value"]) && $get->get('search')['value'] != '') {
            $str = $get->get('search')['value'];

            $qb
                ->where('c.nom LIKE :str')
                ->orWhere('t.nom LIKE :str')
                ->orWhere('c.creation LIKE :str')
                ->orWhere('c.validation LIKE :str')
                ->setParameter('str', '%'.$str.'%')
            ;
        }
        /*if (isset($get['search']) && $get['search']['value'] != '') {
            $str = $get['search']['value'];

            $qb
                ->where('c.nom LIKE :str')
                ->orWhere('t.nom LIKE :str')
                ->setParameter('str', '%'.$str.'%')
            ;
        }*/
        // Pour l affichage parcellaire
        if ($get->get('start') != '' && $get->get('length') != '-1') {
            $start = (int)$get->get('start');
            $length = (int)$get->get('length');
            $qb
                ->setFirstResult($start)
                ->setMaxResults($length)
            ;
        }
        /*if (isset($get['start']) && $get['length'] != '-1') {
            $start = (int)$get['start'];
            $length = (int)$get['length'];
            $qb
                ->setFirstResult($start)
                ->setMaxResults($length)
            ;
        }*/

        // on retourne la requete
        return $qb
            ->getQuery()
            ->getResult()
        ;

    }

    /*
     * Get current classeurs visible for Data Tables for super admin
     *
     * @param integer user id
     * @param array get values of Data Tables
     */
    public function getClasseursVisiblesForDTablesV3SuperAdmin($get) {

        $qb = $this
            ->createQueryBuilder('c')
            ->join('c.type', 't')
            ->addSelect('t')
        ;

        // Pour changer l ordre du tableau
        $colonnes = array('nom', 'creation', 'validation', 'intervenants', 'type', 'status');

        if($get->get('order') !== null) {
            // Condition spéciale pour trier par type par ordre alphabétique
            if ($colonnes[$get->get('order')[0]["column"]] == "type") {
                $order = 't.nom';
            } else {
                $order = 'c.' . $colonnes[$get->get('order')[0]["column"]];
            }
            $qb->orderBy($order, $get->get('order')[0]["dir"]);
        }

        // Pour la recherche dans le tableau
        if (isset($get->get('search')["value"]) && $get->get('search')['value'] != '') {
            $str = $get->get('search')['value'];

            $qb
                ->where('c.nom LIKE :str')
                ->orWhere('t.nom LIKE :str')
                ->setParameter('str', '%'.$str.'%')
            ;
        }

        // Pour l affichage parcellaire
        if ($get->get('start') != '' && $get->get('length') != '-1') {
            $start = (int)$get->get('start');
            $length = (int)$get->get('length');
            $qb
                ->setFirstResult($start)
                ->setMaxResults($length)
            ;
        }

        // on retourne la requete
        return $qb
            ->getQuery()
            ->getResult()
        ;

    }

    public function countClasseurToValidate($userid) {

        return $this
            ->createQueryBuilder('c')
            ->select('c.status', 'c.id')
            ->where('c.status = :sta')
            ->orWhere('c.status = :stat')
            ->setParameter('sta', 1)
            ->setParameter('stat', 4)
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userid)
            ->getQuery()
            ->getResult()
            ;

        /*$qb = $this
            ->createQueryBuilder('c')
            ->select('c.status', 'c.id')
            ->where('c.status = :sta')
            ->orWhere('c.status = :stat')
            ->setParameter('sta', 1)
            ->setParameter('stat', 4)
        ;


        // on retourne la requete
        return $qb
            ->getQuery()
            ->getResult()
            ;*/
    }

    /*public function isDelegatedToUserV2($classeur, $user) {
        $em = $this->getEntityManager();
        $repositorydelegates = $em->getRepository('SesileDelegationsBundle:delegations');
        $liste_delegants = $repositorydelegates->getUsersWhoHasMeAsDelegateRecursively($user);

        $sql = 'SELECT c.* FROM ClasseursUsers cu
                inner join Classeur c on cu.classeur_id = c.id
                WHERE ((c.visibilite = 0 and cu.user_id = :userid) or (c.visibilite > 0 and c.visibilite in (select groupe from UserGroupe where user = :userid)))
                and c.id = :classeurid
                group by cu.classeur_id';

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('SesileClasseurBundle:Classeur', 'c');
        $query = $em->createNativeQuery($sql, $rsm);

        foreach($liste_delegants as $delegant) {
            $query->setParameter('userid', $user->getId());
            $query->setParameter("classeurid", $classeur->getId());
            if(count($query->getResult()) > 0) {
                return true;
            }
        }

        return false;
    }*/

    /**
     * On passe le classeur en parametre et la fonction retourne un tableau d'objet avec users validant du classeur
     *
     * @param Classeur $classeur
     * @return array
     *
     */
    public function getValidant(Classeur $classeur) {
        $em = $this->getEntityManager();
        //var_dump($classeur->getOrdreValidant());

        $tabEtapeClasseur = explode(',',$classeur->getOrdreValidant());
        $usersValidant = array();
     /*   if(!count($tabEtapeClasseur))
        {
            if($classeur->getEtapeDeposante())
            {
                error_log('classeur refusé');
                $usersValidant[] = $classeur->getEtapeDeposante();
                return $usersValidant;
            }
        }*/

        /**
        * Pour réucpérer le validant je récupère le dernier id de la liste getOrdreValidant
        */

        $etapeClasseurs = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneById($tabEtapeClasseur[count($tabEtapeClasseur)-1]);

        if($etapeClasseurs !== null && $classeur->getStatus() != 2) {

            $users = $etapeClasseurs->getUsers();

            $userPacks = $etapeClasseurs->getUserPacks();
            foreach ($userPacks as $userPack) {
                $usersP = $userPack->getUsers();
                $usersValidant = array_merge($usersValidant, $usersP->toArray());
            }

//        $usersValidant = new ArrayCollection(
//            array_merge($users->toArray(), $usersP->toArray())
//        );
//            var_dump($users);
            if($users !== null) {
                $usersValidant = array_merge($users->toArray(), $usersValidant);
            }
            $usersValidant = array_unique($usersValidant);

        }
        elseif ($classeur->getStatus() == 0
            || ($classeur->getStatus() == 4 && $etapeClasseurs === null)
        ) {
            $user = $em->getRepository('SesileUserBundle:User')->findOneById($classeur->getUser());
            $usersValidant[] = $user;
        }
        else {
            $usersValidant = array();
        }

        return $usersValidant;

    }


    /**
     * On passe le classeur en parametre et la fonction retourne un tableau d'objet avec users validant du classeur de l étape précedente
     *
     * @param Classeur $classeur
     * @return array
     */
    public function getPrevValidant(Classeur $classeur) {

        $em = $this->getEntityManager();
        $etapeClasseurs = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneBy(
            array(
                'classeur' => $classeur->getId(),
                'ordre' => $classeur->getOrdreEtape()
            )
        );

        if ($etapeClasseurs !== null) {
            $users = $etapeClasseurs->getUsers();
            $usersValidant = array();

            $userPacks = $etapeClasseurs->getUserPacks();
            foreach ($userPacks as $userPack) {
                $usersP = $userPack->getUsers();
                $usersValidant = array_merge($usersValidant, $usersP->toArray());
            }

//        $usersValidant = array_merge($users->toArray(), $usersP->toArray());
            $usersValidant = array_merge($users->toArray(), $usersValidant);
            $usersValidant = array_unique($usersValidant);
        }
        else {
            $usersValidant = array();
        }

        return $usersValidant;
    }



    public function getPrevValidantForRetract(Classeur $classeur) {

        $prevValidant = explode(',', $classeur->getCircuit());
        $prevValidant = end($prevValidant);
       // var_dump($prevValidant);exit;
        // Pour l amelioration du validant qui doit se retracter...
        //var_dump($prevValidant);
        if (!$prevValidant) {
            $prevValidant = $classeur->getUser();
        }

        return $prevValidant;
    }

    /**
     * Fonction pour valider les classeurs
     *
     * @param Classeur $classeur
     * @return Classeur
     */
    public function validerClasseur (Classeur $classeur) {

        $ordreEtape = $classeur->getOrdreEtape();
        $ordreEtape++;
//        $tabEtapeClasseur = $classeur->getOrdreEtape());
//        $ordreEtape = count($tabEtapeClasseur);


        $em = $this->getEntityManager();
        $currentEtape = $em->getRepository('SesileUserBundle:EtapeClasseur')->findBy(
            array('classeur' => $classeur)
        );


        /**
         * Pour réucpérer le validant je récupère le dernier id de la liste getOrdreValidant
         */
        $tabEtapeClasseur = explode(',',$classeur->getOrdreValidant());
        $etapeClasseurs = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneById($tabEtapeClasseur[count($tabEtapeClasseur)-1]);


//        $nbEtapesClasseur = count($classeur->getEtapeClasseurs()) - 1;
        $nbEtapesClasseur = count($classeur->getEtapeClasseurs());


//        var_dump($nbEtapesClasseur, $ordreEtape);
        // Si c est la derniere etape
        if($nbEtapesClasseur == $ordreEtape) {
            $classeur->setStatus(2);
        }
        else {
            $classeur->setStatus(1);
            $currentEtapeId = $currentEtape[$ordreEtape]->getId();
//            $classeur->setOrdreValidant($classeur->getOrdreValidant() . ',' . $currentEtape->getId());
            $classeur->setOrdreValidant($classeur->getOrdreValidant() . ',' . $currentEtapeId);
        }

        $classeur->setOrdreEtape($ordreEtape);

        return $classeur;
    }

    /**
     * Retourne l utilisateur qui doit valider, celui qui a les droits sur le classeur
     *
     * @param Classeur $classeur
     * @param $user
     * @return object
     */
    public function classeurValidator(Classeur $classeur, $user) {

        $em = $this->getEntityManager();

        $classeurValidants = $this->getValidant($classeur);

        $classeurValidantsId = array();
        foreach ($classeurValidants as $classeurValidant) {
            $classeurValidantsId[] = $classeurValidant->getId();
        }

        $delegants = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($user);

        $delegantsId = array();
        foreach ($delegants as $delegant) {
            $delegantsId[] = $delegant->getDelegant()->getId();
        }

        // Avec délégation
        $userValidant = array_intersect($classeurValidantsId, $delegantsId);

        if ($userValidant) {
            $userValidant = $userValidant[0];
            $validator = $em->getRepository('SesileUserBundle:User')->find($userValidant);
        }
        // Sans délégation
        else {
            $validator = $user;
        }
        var_dump($validator->getId(), $validator->getNom(), $validator->getPrenom());

        return $validator;

    }

    /**
     * Retourne true or false selon si le classeur est délégué ou pas
     *
     * @param Classeur $classeur
     * @param $user
     * @return bool true|false
     */
    public function isDelegatedToUser(Classeur $classeur, $user) {

        $em = $this->getEntityManager();

        $classeurValidants = $this->getValidant($classeur);

        $classeurValidantsId = array();
        foreach ($classeurValidants as $classeurValidant) {
            $classeurValidantsId[] = $classeurValidant->getId();
        }

        $delegants = $em->getRepository('SesileDelegationsBundle:Delegations')->getDelegantsForUser($user);

        $delegantsId = array();
        foreach ($delegants as $delegant) {
            $delegantsId[] = $delegant->getDelegant()->getId();
        }

        // Avec délégation
        $userValidant = array_intersect($classeurValidantsId, $delegantsId);

        if ($userValidant) {
            return true;
        }
        // Sans délégation
        else {
            return false;
        }

    }

    /**
     * MAJ de l etat de la visibilité, nom, description, date de validation
     *
     * @param $request
     * @param $classeur
     */
    public function updateInfosClasseurs($request, $id) {
        if (null !== $request && $request->isMethod('post')) {
            $em = $this->getEntityManager();
            $classeur = $em->getRepository('SesileClasseurBundle:Classeur')->findOneById($id);
            $visibilite = $request->get("visibilite");
            $classeur->setVisibilite($visibilite);
            $classeur->setNom($request->get("name"));
            $classeur->setDescription($request->get("desc"));
            list($d, $m, $a) = explode("/", $request->request->get('validation'));
            $valid = new \DateTime($m . "/" . $d . "/" . $a);
            $classeur->setValidation($valid);

            // MAJ de la visibilite
            $this->set_user_visible($classeur, $visibilite);

            $em->flush();
        }
    }

    /**
     * Fonction permettant la mise a jour de la visibilite
     *
     * @param $classeur
     * @param $visibilite
     */
    public function set_user_visible ($classeur, $visibilite) {
//        $em = $this->getDoctrine()->getManager();
        $em = $this->getEntityManager();
        $users = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
        $users[] = $classeur->getUser();

        if ($visibilite != 2 && $visibilite != 3) {
            $usersCV = $this->classeur_visible($visibilite, $users);
            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV->getId());
                $classeur->addVisible($userVisible);
            }

        }

        // Si la visibilite du classeur est prive a partir de moi
        elseif ($visibilite == 2) {
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);

            // On vide la table many to many
            $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        }

        // Si la visibilite du classeur est service organisationnel (et le circuit)
        elseif ($visibilite == 3) {

            $usersVisible = $classeur->getVisible();
            $usersAlreadyVisible = array();
            foreach ($usersVisible as $userV) {
                $usersAlreadyVisible[] = $userV->getId();
            }
            $usersCV = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
            $usersCV = array_unique($usersCV);

            $usersCV = array_diff($usersCV, $usersAlreadyVisible);
            // On vide la table many to many
            // $classeur->getVisible()->clear();
            foreach ($usersCV as $userCV) {
                $userVisible = $em->getRepository('SesileUserBundle:User')->findOneById($userCV);
                $classeur->addVisible($userVisible);
            }
        }
    }

}
