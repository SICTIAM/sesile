<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Sesile\MainBundle\Entity\Collectivite;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository {

    public function addUser(Collectivite $collectivite, $email, $username) {
        $em = $this->getEntityManager();

        if (strpos($username, ' ')) {
            $nom = explode(' ', $username)[0];
            $prenom = explode(' ', $username)[1];
        } else {
            $nom = $username;
            $prenom = $username;
        }

        $user = new User();
        $user->setUsername($email);
        $user->setPrenom($nom);
        $user->setNom($prenom);
        $user->setEmail($email);
        $user->setPassword(md5(uniqid(rand(), true)));
        $user->setSesileVersion(0);
        $user->setCollectivite($collectivite);
        $user->setEnabled(true);
        $user->addCollectivity($collectivite);

        $em->persist($user);
    }

    public function findByUserPacks($userPack) {

        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.userPacks', 'u')
            ->where('u.id = :userid')
            ->setParameter('userid', $userPack)
            ->getQuery()
            ->getResult();
    }

    public function uploadFile($avatar, $user, $dirPath) {

        if ($avatar) {
            if ($user->getPath()) {
                $user->removeUpload($dirPath);
            }
            $avatarName = sha1(uniqid(mt_rand(), true)) . '.' . $avatar->guessExtension();
            $user->setPath($avatarName);
            $avatar->move(
                $dirPath,
                $avatarName
            );
        }
        return $user;
    }

    public function uploadSignatureFile($file, $user, $dirPath) {

        if ($file) {
            if ($user->getPathSignature()) {
                $user->removeUpload($dirPath);
            }
            $fileName = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
            $user->setPathSignature($fileName);
            $file->move(
                $dirPath,
                $fileName
            );
        }
        return $user;
    }

    public function getClasseurIdValidableForUser($user) {
        $classeursId = array();

        $etapeClasseurs = $user->getEtapeClasseurs();
        foreach ($etapeClasseurs as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValidante()) {
                $classeursId[] = $etapeClasseur->getClasseur()->getId();
            }
        }

        $userPacks = $user->getUserPacks();
        foreach ($userPacks as $userPack) {
            $packEtapeClasseurs = $userPack->getEtapeClasseurs();
            foreach ($packEtapeClasseurs as $packEtapeClasseur) {
                if ($packEtapeClasseur->getEtapeValidante()) {
                    $classeursId[] = $packEtapeClasseur->getClasseur()->getId();
                }
            }
        }
        return array_unique($classeursId);
    }

    public function getClasseurIdRetractableForUser($user) {
        $classeursId = array();
        $em = $this->getEntityManager();

        $etapeClasseurs = $user->getEtapeClasseurs();
        foreach ($etapeClasseurs as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValidante()) {
                $etapeClasseurRetractable = $em->getRepository('SesileUserBundle:EtapeClasseur')->getPreviousEtape($etapeClasseur);

                if ($etapeClasseurRetractable && $etapeClasseurRetractable->getUserValidant() == $user) {
                    $classeursId[] = $etapeClasseurRetractable->getClasseur()->getId();
                }
            }
        }

        $userPacks = $user->getUserPacks();
        foreach ($userPacks as $userPack) {
            $packEtapeClasseurs = $userPack->getEtapeClasseurs();
            foreach ($packEtapeClasseurs as $packEtapeClasseur) {
                if ($packEtapeClasseur->getEtapeValidante()) {

                    $etapeClasseurRetractable = $em->getRepository('SesileUserBundle:EtapeClasseur')->getPreviousEtape($etapeClasseur);
                    if ($etapeClasseurRetractable && $etapeClasseurRetractable->getUserValidant() == $user) {
                        $classeursId[] = $etapeClasseurRetractable->getClasseur()->getId();
                    }

                }
            }
        }

        $classeursDepose = $em->getRepository('SesileClasseurBundle:Classeur')->findBy(array(
            'user' => $user,
            'status'=> 1
        ));
        foreach ($classeursDepose as $classeurDepose) {
            if($classeurDepose->countEtapeValide() == 0) {
                $classeursId[] = $classeurDepose->getId();
            }
        }

        return array_unique($classeursId);
    }

    /**
     * Fonction pour savoir si un user est dans des classeurs
     * @param User $user
     * @return bool
     */
    public function isUserInClasseurs(User $user) {
        // On récupère tous les classeurs
        $em = $this->getEntityManager();
        $classeurs = $em->getRepository('SesileClasseurBundle:Classeur')->findAll();

        foreach($classeurs as $classeur) {
            $usersClasseur = $em->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
            if (in_array($user, $usersClasseur)) return true;
        }
        return false;
    }

    public function findByNameOrFirstName($value, $collectiviteId)
    {
        return $this
            ->createQueryBuilder('U')
            ->join('U.collectivities', 'C')
            ->where('C.id = :collectiviteId')
            ->setParameter('collectiviteId', $collectiviteId)
            ->andWhere('CONCAT(U.Nom, \' \', U.Prenom) LIKE :value')
            ->setParameter('value', '%' .$value. '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $user
     * @param $userObject array of the user object inside the request->get('user')
     * @param Collectivite $collectivite
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createUserFromOzwillo ($user, $userObject, Collectivite $collectivite) {
        $em = $this->getEntityManager();
        if (!$user) {
            $user = new User();
            $user->setEnabled(true);
            $user->setUsername($userObject['name']);
            $user->setEmail($userObject['email_address']);
            $user->setPlainPassword('sictiam');
            $user->setOzwilloId($userObject['id']);
        }

        $user->setCollectivite($collectivite);
        $user->addCollectivity($collectivite);
        $user->addRole("ROLE_ADMIN");
        $em->persist($user);

        $em->flush();

    }

    /**
     * @param string $collectiviteId
     *
     * @return array
     */
    public function getUsersByCollectivityId($collectiviteId)
    {
        return $this
                ->createQueryBuilder('U')
            ->select('U.id, U.Nom as nom, U.Prenom as prenom, U.email, U.username, U.ozwilloId, U.ville, U.cp, U.pays, U.departement, U.role, U.qualite')
                ->join('U.collectivities', 'C')
                ->where('C.id = :collectiviteId')
                ->setParameter('collectiviteId', $collectiviteId)
                ->getQuery()
                ->getArrayResult();
    }
}
