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
    protected $Nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Prenom", type="string", length=255)
     */
    protected $Prenom;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="user")
     */
    protected $delegations;

    public function setPrenom($Prenom)
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function setNom($Nom)
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * renvoie le nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->Nom;
    }


    /**
     * renvoie le prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->Prenom;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }


    public function getCredentialsExpireAt()
    {
        return $this->getCredentialsExpireAt();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add delegations
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegations
     * @return User
     */
    public function addDelegation(\Sesile\DelegationsBundle\Entity\Delegations $delegations)
    {
        $this->delegations[] = $delegations;

        return $this;
    }

    /**
     * Remove delegations
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegations
     */
    public function removeDelegation(\Sesile\DelegationsBundle\Entity\Delegations $delegations)
    {
        $this->delegations->removeElement($delegations);
    }

    /**
     * Get delegations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDelegations()
    {
        return $this->delegations;
    }
}