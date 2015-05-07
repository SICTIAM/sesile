<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtapeGroupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\EtapeGroupeRepository")
 */
class EtapeGroupe
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="etapeGroupes", cascade={"persist"})
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", inversedBy="etapeGroupes", cascade={"persist"})
     */
    private $userPacks;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\Groupe", inversedBy="$etapeGroupes")
     * @ORM\JoinColumn(name="groupe", referencedColumnName="id")
     *
     */
    protected $groupe;

    
    /**
     * @ORM\ManyToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="etapeGroupes", cascade={"persist"})
     */
    private $classeurs;

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
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userPacks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->classeurs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     * @return EtapeGroupe
     */
    public function addUser(\Sesile\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     */
    public function removeUser(\Sesile\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     * @return EtapeGroupe
     */
    public function addUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks[] = $userPacks;
    
        return $this;
    }

    /**
     * Remove userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     */
    public function removeUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks->removeElement($userPacks);
    }

    /**
     * Get userPacks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserPacks()
    {
        return $this->userPacks;
    }

    

    /**
     * Get groupe
     *
     * @return \Sesile\ClasseurBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Add classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     * @return EtapeGroupe
     */
    public function addClasseur(\Sesile\ClasseurBundle\Entity\Classeur $classeurs)
    {
        $this->classeurs[] = $classeurs;
    
        return $this;
    }

    /**
     * Remove classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     */
    public function removeClasseur(\Sesile\ClasseurBundle\Entity\Classeur $classeurs)
    {
        $this->classeurs->removeElement($classeurs);
    }

    /**
     * Get classeurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClasseurs()
    {
        return $this->classeurs;
    }

    /**
     * Set groupe
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupe
     * @return EtapeGroupe
     */
    public function setGroupe(\Sesile\UserBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;
    
        return $this;
    }
}