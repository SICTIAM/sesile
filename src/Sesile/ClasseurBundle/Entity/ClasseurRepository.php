<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\EntityRepository;
use phpDocumentor\Reflection\Types\Array_;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Sesile\UserBundle\Entity\User;

/**
 * ClasseurRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClasseurRepository extends EntityRepository {

    /**
     * @param $orgId collectivite Id
     * @param $userId
     *
     * @return array
     */
    public function getAllClasseursVisibles ($orgId, $userId)
    {

        $sort = "c.creation";
        $order = "DESC";

        $classeurs =  $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userId)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->where('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->orderBy($sort, $order)
            ->getQuery()
            ->getResult()
        ;

        $classeurs = $this->addClasseursValue($classeurs, $userId);

        return $classeurs;
    }

    /**
     * @param $collectivityId
     * @param $userId
     * @param $name
     *
     * @return array
     */
    public function searchClasseurs($collectivityId, $userId, $name)
    {
        return $this
            ->createQueryBuilder('c')
            ->select('c.id, c.nom, c.description')
            ->join('c.visible', 'v', 'WITH', 'v.id = :userId')
            ->join('c.user', 'u')
            ->where('c.collectivite = :orgId')
            ->andWhere('c.nom like :name')
            ->setParameter('userId', $userId)
            ->setParameter('orgId', $collectivityId)
            ->setParameter('name', '%'.$name.'%')
            ->orderBy("c.creation", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $orgId collectivite id
     * @param $userId
     * @param $sort
     * @param $order
     * @param $limit
     * @param $start
     *
     * @return array
     */
    public function getClasseursVisibles ($orgId, $userId, $sort, $order, $limit, $start)
    {
        ($sort == "type") ? $sort = "t.nom" : $sort = "c.".$sort;

        $classeurs =  $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userId)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->where('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->orderBy($sort, $order)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $classeurs = $this->addClasseursValue($classeurs, $userId);

        return $classeurs;
    }

    /**
     * @param $orgId collectivite id
     * @param $classeursId
     * @param $sort
     * @param $order
     * @param $limit
     * @param $start
     * @param $userId
     * @return array
     */
    public function getClasseursValidable ($orgId, $classeursId, $sort, $order, $limit, $start, $userId)
    {
        ($sort == "type") ? $sort = "t.nom" : $sort = "c.".$sort;

        $status = array(0,1,4);

        $classeurs = $this
            ->createQueryBuilder('c')
            ->where('c.id IN (:id)')
            ->andWhere('c.status IN (:status)')
            ->setParameter('id', $classeursId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->orderBy($sort, $order)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $classeurs = $this->addClasseursValue($classeurs, $userId);

        return $classeurs;

    }

    public function countClasseursValidable($orgId, $classeursId) {
        $status = array(0,1,4);

        $count = $this
            ->createQueryBuilder('c')
            ->where('c.id IN (:id)')
            ->andWhere('c.status IN (:status)')
            ->setParameter('id', $classeursId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->select('COUNT(c)')
            ->getQuery()
            ->getResult();

        return $count;
    }

    /**
     * @param $orgId
     * @param $classeursId
     * @param $sort
     * @param $order
     * @param $limit
     * @param $start
     * @param $userId
     * @return array
     */
    public function getClasseursRetractable ($orgId, $classeursId, $sort, $order, $limit, $start, $userId)
    {

        ($sort == "type") ? $sort = "t.nom" : $sort = "c.".$sort;

        $status = 1;

        $classeurs = $this
            ->createQueryBuilder('c')
            ->where('c.id IN (:id)')
            ->andWhere('c.status = :status')
            ->setParameter('id', $classeursId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->orderBy($sort, $order)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $classeurs = $this->addClasseursValue($classeurs, $userId);

        return $classeurs;

    }

    public function countClasseursRetractable ($orgId, $classeursId)
    {
        $status = 1;

        $count = $this
            ->createQueryBuilder('c')
            ->where('c.id IN (:id)')
            ->andWhere('c.status = :status')
            ->setParameter('id', $classeursId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->select('COUNT(c)')
            ->getQuery()
            ->getResult();

        return $count;
    }

    /**
     * @param $orgId
     * @param $userId
     * @param $sort
     * @param $order
     * @param $limit
     * @param $start
     *
     * @return array
     */
    public function getClasseursremovable ($orgId, $userId, $sort, $order, $limit, $start)
    {

        ($sort == "type") ? $sort = "t.nom" : $sort = "c.".$sort;

        $status = 3;

        $classeurs = $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->andWhere('c.status = :status')
            ->setParameter('id', $userId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->orderBy($sort, $order)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;

        $classeurs = $this->addClasseursValue($classeurs, $userId);

        return $classeurs;

    }

    public function countClasseursremovable ($orgId, $userId)
    {
        $status = 3;

        $classeurs = $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->andWhere('c.status = :status')
            ->setParameter('id', $userId)
            ->setParameter('status', $status)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->select('COUNT(c)')
            ->getQuery()
            ->getResult();

        return $classeurs;

    }

    public function getClasseursById($userId, $id) {

        $classeur =  $this
            ->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult()
        ;

        $classeur = $this->addClasseurValue($classeur, $userId);

        return $classeur;
    }

    public function addClasseursValue($classeurs, $userId) {
        foreach ($classeurs as $classeur) {
            $this->addClasseurValue($classeur, $userId);
        }
        return $classeurs;
    }

    public function addClasseurValue($classeur, $userId) {
        $this->isEtapeDeposante($classeur);
        $this->isClasseurValidableByUser($classeur, $userId);
        $this->isClasseurSignable($classeur, $userId);
        $this->isClasseurRefusable($classeur, $userId);
        $this->isClasseurRetractableByUser($classeur, $userId);
        $this->isClasseurRemovableByUser($classeur, $userId);
        $this->isClasseurDeletableByUser($classeur, $userId);
        return $classeur;
    }

    public function isClasseurDeletableByUser (Classeur $classeur, $userId) {
        $em = $this->getEntityManager();
        $user = $em->getRepository('SesileUserBundle:User')->findOneById($userId);
        if($classeur->getStatus() === 3 && $user->hasRole('ROLE_ADMIN')) {
            $classeur->setDeletable(true);
        }
    }

    public function isClasseurRemovableByUser(Classeur $classeur, $userId) {
        $em = $this->getEntityManager();
        $repositoryEtapeClasseur = $em->getRepository('SesileUserBundle:EtapeClasseur');
        $validantUserId = null;
        $usersPreviousEtape = array();
        if($classeur->getEtapeValidante()) {
            $etapeRetractable = $repositoryEtapeClasseur->getPreviousEtape($classeur->getEtapeValidante());
            if($etapeRetractable !== false) {
                $usersPreviousEtape = $repositoryEtapeClasseur->getUsersForEtape($etapeRetractable);
            }
        }
        if($classeur->getStatus() === 1 && (in_array($userId, array_column($usersPreviousEtape, 'id')) OR $userId === $classeur->getUser()->getId())) {
            $classeur->setRemovable(true);
        }
    }


    /**
     * Function pour tester si le classeur est signable
     * @param Classeur $classeur
     * @return Classeur
     */
    public function isClasseurSignable(Classeur $classeur, $userId) {

        if($this->userIsInValidatingStep($classeur->getEtapeValidante(), $userId) && $classeur->isAtLastValidant()){
            $docs = $classeur->getDocuments();
            foreach($docs as $doc){
                if(in_array($doc->getType(), $classeur->typeSignable)){
                    $classeur->setSignableAndLastValidant(true);
                    return $classeur;
                }
            }
        }
        $classeur->setSignableAndLastValidant(false);
        return $classeur;
    }

    public function isClasseurValidableByUser(Classeur $classeur, $userId) {
        // @todo verify this condition and move it in a service
        if(
            ($this->userIsInValidatingStep($classeur->getEtapeValidante(), $userId) AND $classeur->getStatus() != 0 AND $classeur->getStatus() != 2 AND $classeur->getStatus() != 3)
            AND !($classeur->getNextEtapeValidante() === false AND $classeur->getType()->getNom() == "Helios")
            OR ($classeur->getEtapeDeposante() AND $classeur->getUser()->getId() === $userId )
        ) {
            $classeur->setValidable(true);
        } else {
            $classeur->setValidable(false);
        }
        return $classeur;
    }

    public function isClasseurRefusable(Classeur $classeur, $userId) {
        if ($this->userIsInValidatingStep($classeur->getEtapeValidante(), $userId) && !$classeur->getEtapeDeposante()) {
            $classeur->setRefusable(true);
        } else {
            $classeur->setRefusable(false);
        }
    }

    public function isClasseurRetractableByUser(Classeur $classeur, $userId) {
        if(($userId === $this->getUserIdValidant($classeur->getEtapeValidante()) OR ($classeur->countEtapeValide() === 0 AND $userId == $classeur->getUser()->getId())) AND $classeur->getStatus() == 1) {
            $classeur->setRetractable(true);
        }
        else {
            $classeur->setRetractable(false);
        }
        return $classeur;
    }

    public function isEtapeDeposante (Classeur $classeur) {
        if (!$this->isOneEtapevalidante($classeur) AND $classeur->getStatus() !== 2 AND $classeur->getStatus() !== 3) {
            $classeur->setEtapeDeposante(true);
        } else {
            $classeur->setEtapeDeposante(false);
        }
        return $classeur;
    }

    public function countEtapesValide (Classeur $classeur) {
        $count = 0;
        foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValide()) $count++;
        }

        return $count;
    }

    public function isOneEtapevalidante (Classeur $classeur) {
        foreach ($classeur->getEtapeClasseurs() as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValidante()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fonction permettant la mise a jour de la visibilite
     *
     * @param Classeur $classeur
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     */
    public function setUserVisible(Classeur $classeur) {
        $em = $this->getEntityManager();

        switch ($classeur->getVisibilite()) {
            // Privé soit le circuit
            case 0:
                $users = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
                break;

            // Public
            case 1:
                $users = $em->getRepository('SesileUserBundle:User')->findByCollectivite($classeur->getCollectivite());
                break;

            // Privé à partir de moi
            case 2:
                $users = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsersAfterMe($classeur);
                break;

            // Pour le service organisationnel (et le circuit)
            case 3:
                $usersGroupe = $em->getRepository('SesileUserBundle:Groupe')->findUsers($classeur->getCircuitId());
                $usersCircuit = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
                $users = array_merge($usersGroupe, $usersCircuit);
                break;
        }

        $users[] = $classeur->getUser();
        if (is_array($classeur->getCopy())) {
            $users = array_merge($users, $classeur->getCopy());
        }
        $users = array_unique($users);
        if ($classeur->getVisible()) {
            $classeur->getVisible()->clear();
        }

        foreach ($users as $user) {
            $classeur->addVisible($user);
        }

        $em->persist($classeur);
        $em->flush();

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
    }


    /**
     * On passe le classeur en parametre et la fonction retourne un tableau d'objet avec users validant du classeur
     *
     * @param Classeur $classeur
     * @return array
     *
     */
    public function getValidant(Classeur $classeur) {
        $em = $this->getEntityManager();
        $etapeValidante = $em->getRepository('SesileUserBundle:EtapeClasseur')->findOneBy(array(
            'classeur'  => $classeur,
            'etapeValidante' => true
        ));

        return $em->getRepository('SesileUserBundle:EtapeClasseur')->getUsersForEtape($etapeValidante);
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

            $usersValidant = array_merge($users->toArray(), $usersValidant);
            $usersValidant = array_unique($usersValidant);
        }
        else {
            $usersValidant = array();
        }

        return $usersValidant;
    }


    /**
     * Fonction pour valider un classeur
     *
     * @param Classeur $classeur
     * @param User $user
     * @return Classeur
     */
    public function validerClasseur (Classeur $classeur, User $user) {

        foreach ($classeur->getEtapeClasseurs() as $etape) {
            if ($etape->getEtapeValidante()) {
                $etape->setEtapeValide(1);
                $etape->setEtapeValidante(0);
                $etape->setUserValidant($user);
            }

            if (!$etape->getEtapeValide()) {
                $etape->setEtapeValidante(1);
                $classeur->setStatus(1);
                break;
            } else {
                $classeur->setStatus(2);
            }

        }
        return $classeur;
    }

    public function retractClasseur (Classeur $classeur) {

        $etapeValidante = $classeur->getEtapeValidante();

        $newEtapeValidante = $classeur->getPrevEtapeValidante();
        if ($newEtapeValidante) {
            $newEtapeValidante->setEtapeValidante(1);
            $newEtapeValidante->setEtapeValide(0);
            $newEtapeValidante->setUserValidant(null);
        }

        $etapeValidante->setEtapeValidante(0);
        $classeur->setStatus(4);
        return $classeur;

    }

    public function removeClasseur (Classeur $classeur) {
        $classeur->setStatus(3);
        return $classeur;
    }

    public function refuseClasseur (Classeur $classeur, $motif = '') {

        $classeur->setStatus(0);
        $classeur->setMotifRefus($motif);
        $etapesClasseur = $classeur->getEtapeClasseurs();
        foreach ($etapesClasseur as $etapeClasseur) {
            $etapeClasseur->setEtapeValidante(false);
            $etapeClasseur->setEtapeValide(false);
            $etapeClasseur->setUserValidant(null);
        }
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

    public function setUserCopyForClasseur(Classeur $classeur, $usersCopy) {
        $em = $this->getEntityManager();

        if (null !== $classeur->getCopy()) {
            foreach ($classeur->getCopy() as $copy) {
                $classeur->removeCopy($copy);
            }
        }

        $usersCopyVisible = array();

        if ($usersCopy !== null) {

            foreach ($usersCopy as $userCopy) {
                list($cat, $id) = explode('-', $userCopy);

                if ($cat == "userpack") {

                    $userPack = $em->getRepository("SesileUserBundle:UserPack")->findOneById($id);
                    $users = $userPack->getUsers();
                    foreach ($users as $user) {
                        $classeur->addCopy($user);

                        is_array($classeur->getVisible()) ? $visible = $classeur->getVisible() : $visible = $classeur->getVisible()->toArray();
                        if (!in_array($user ,$visible)) {
                            $classeur->addVisible($user);
                        }
                    }
                } elseif ($cat == "user") {
                    $user = $em->getRepository("SesileUserBundle:User")->findOneById($id);
                    $classeur->addCopy($user);

                    is_array($classeur->getVisible()) ? $visible = $classeur->getVisible() : $visible = $classeur->getVisible()->toArray();
                    if (!in_array($user ,$visible)) {
                        $classeur->addVisible($user);
                    }
                }
            }
            $em->flush();
        }
        return $usersCopyVisible;

    }

    public function findDelayedClasseursByUser($userId) {
        $classeurs =  $this
            ->createQueryBuilder('c')
            ->select('c.id, c.nom, c.validation')
            ->andWhere('c.validation <= :date')
            ->setParameter('date', 'now()')
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('c.status = 0 OR c.status = 1 OR c.status = 4')
            ->getQuery()
            ->getResult();

        return $classeurs;
    }

    private function getUserIdValidant($etapeValidante)
    {
        $em = $this->getEntityManager();
        $validantUserId = null;
        if ($etapeValidante){
            $etapeRetractable = $em->getRepository('SesileUserBundle:EtapeClasseur')->getPreviousEtape($etapeValidante);
            if ($etapeRetractable && $etapeRetractable->getUserValidant()) {
                return $validantUserId = $etapeRetractable->getUserValidant()->getId();
            }
        }
    }

    /**
     * @param null $collectivityId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getExpiredClasseurs($collectivityId = null)
    {

        $sql = 'select c.id
                from Classeur c
                LEFT JOIN Collectivite col on c.collectivite_id = col.id
                where (c.creation + INTERVAL col.deleteClasseurAfter DAY) < NOW()
        ';
        $params = [];
        if ($collectivityId) {
            $sql .= ' AND c.collectivite_id = :collectivityId';
            $params['collectivityId'] = $collectivityId;
        }

        return $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function userIsInValidatingStep($etapeValidante, $userId) {
        if ($etapeValidante && in_array($userId, $etapeValidante->getValidantUsersId())) {
            return true;
        } else {
            return false;
        }
    }

    public function countVisibleClasseur($orgId, $userId) {
        return $this
            ->createQueryBuilder('c')
            ->join('c.visible', 'v', 'WITH', 'v.id = :id')
            ->setParameter('id', $userId)
            ->join('c.type', 't')
            ->addSelect('t')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->where('c.collectivite = :orgId')
            ->setParameter('orgId', $orgId)
            ->select('COUNT(c)')
            ->getQuery()
            ->getResult();
    }
}
