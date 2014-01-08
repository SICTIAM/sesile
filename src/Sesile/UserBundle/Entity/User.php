<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;


/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=255)
     */
    protected $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Prenom", type="string", length=255)
     */
    protected $prenom;

    public function setPrenom($prenom)
    {
        $this->username = $prenom;

        return $this;
    }

    public function setNom($nom)
    {
        $this->username = $nom;

        return $this;
    }

    /**
     * renvoie le nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }


    /**
     * renvoie le prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getExpiresAt() {
        return $this->expiresAt;
    }


    public function getCredentialsExpireAt() {
        return $this->getCredentialsExpireAt();
    }
}
