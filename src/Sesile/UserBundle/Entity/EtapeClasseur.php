<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtapeClasseur
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\EtapeClasseurRepository")
 */
class EtapeClasseur
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
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="etapeClasseurs", cascade={"persist"})
     */
    private $users;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="$etapeClasseurs")
     * @ORM\JoinColumn(name="classeur", referencedColumnName="id")
     *
     */
    protected $classeur;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", inversedBy="$etapeClasseurs", cascade={"persist"})
     */
    private $userPacks;


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
     * Set classeur
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeur
     * @return EtapeClasseur
     */
    public function setClasseur(\Sesile\ClasseurBundle\Entity\Classeur $classeur = null)
    {
        $this->classeur = $classeur;
    
        return $this;
    }

    /**
     * Get classeur
     *
     * @return \Sesile\ClasseurBundle\Entity\Classeur 
     */
    public function getClasseur()
    {
        return $this->classeur;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userPacks = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     * @return EtapeClasseur
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
     * Add users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     * @return EtapeClasseur
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
}