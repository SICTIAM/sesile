<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;

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
     * @Groups({"listEtapeClasseur"})
     */
    private $users;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="etapeClasseurs")
     * @ORM\JoinColumn(name="classeur", referencedColumnName="id")
     *
     */
    protected $classeur;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", inversedBy="etapeClasseurs", cascade={"persist"})
     * @Groups({"listEtapeClasseur"})
     */
    private $userPacks;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     *
     */
    private $ordre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="EtapeValidante", type="boolean", nullable=true, options={"default" = false})
     * @Groups({"listEtapeClasseur"})
     *
     */
    private $etapeValidante;

    /**
     * @var boolean
     *
     * @ORM\Column(name="etapeValide", type="boolean", nullable=true, options={"default" = false})
     * @Groups({"listEtapeClasseur"})
     *
     */
    private $etapeValide;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="etapeValide")
     * @ORM\JoinColumn(name="userValidant", referencedColumnName="id")
     * @Groups({"listEtapeClasseur"})
     *
     */
    private $userValidant;

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

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return EtapeClasseur
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    
        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    public function countUserPacksUsers() {

        $nbUsers =  count($this->getUsers());
        $nbUserPacks = count($this->getUserPacks());
        $total = $nbUsers + $nbUserPacks;

        return $total;
    }

    /**
     * Set userValidant
     *
     * @param \Sesile\UserBundle\Entity\User $userValidant
     *
     * @return EtapeClasseur
     */
    public function setUserValidant(\Sesile\UserBundle\Entity\User $userValidant = null)
    {
        $this->userValidant = $userValidant;

        return $this;
    }

    /**
     * Get userValidant
     *
     * @return \Sesile\UserBundle\Entity\User
     */
    public function getUserValidant()
    {
        return $this->userValidant;
    }

    /**
     * Set etapeValide
     *
     * @param boolean $etapeValide
     *
     * @return EtapeClasseur
     */
    public function setEtapeValide($etapeValide)
    {
        $this->etapeValide = $etapeValide;

        return $this;
    }

    /**
     * Get etapeValide
     *
     * @return boolean
     */
    public function getEtapeValide()
    {
        return $this->etapeValide;
    }

    /**
     * Set etapeValidante
     *
     * @param boolean $etapeValidante
     *
     * @return EtapeClasseur
     */
    public function setEtapeValidante($etapeValidante)
    {
        $this->etapeValidante = $etapeValidante;

        return $this;
    }

    /**
     * Get etapeValidante
     *
     * @return boolean
     */
    public function getEtapeValidante()
    {
        return $this->etapeValidante;
    }
}
