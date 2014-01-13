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
    protected $delegationsRecues;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="delegant")
     */
    protected $delegationsDonnees;

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
     * Add delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     * @return User
     */
    public function addDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues)
    {
        $this->delegationsRecues[] = $delegationsRecues;
    
        return $this;
    }

    /**
     * Remove delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     */
    public function removeDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues)
    {
        $this->delegationsRecues->removeElement($delegationsRecues);
    }

    /**
     * Get delegationsRecues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegationsRecues()
    {
        return $this->delegationsRecues;
    }

    /**
     * Add delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     * @return User
     */
    public function addDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees[] = $delegationsDonnees;
    
        return $this;
    }

    /**
     * Remove delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     */
    public function removeDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees->removeElement($delegationsDonnees);
    }

    /**
     * Get delegationsDonnees
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDelegationsDonnees()
    {
        return $this->delegationsDonnees;
    }
}