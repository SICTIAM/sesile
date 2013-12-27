<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classeur
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseurRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Classeur
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
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation", type="datetime")
     */
    private $creation;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var integer
     * En cours = 1 finalisé = 2, refusé = 0, retiré = 3, retracté = 4
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
 * @var int
 *
 * @ORM\Column(name="user", type="integer")
 *
 * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs")
 * @ORM\JoinColumn(name="user", referencedColumnName="id")
 *
 */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="validant", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs_a_valider")
     * @ORM\JoinColumn(name="validant", referencedColumnName="id")
     *
     */
    private $validant;

    /**
     * @var int
     *
     * -1 = privé, 0 = public, id user = à partir de
     * @ORM\Column(name="visibilite", type="integer")
     *
     */
    private $visibilite;

    /**
     * @var string
     *
     * @ORM\Column(name="circuit", type="string", length=255)
     */
    private $circuit;

    /**
     *
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\ClasseurUsers", mappedBy="userId")
     */
    private $users;

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
     * Set nom
     *
     * @param string $nom
     * @return Classeur
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Classeur
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return Classeur
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    
        return $this;
    }

    /**
     * Get creation
     *
     * @return \DateTime 
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationValue()
    {
        $this->creation = new \DateTime();
    }

    /**
     * Set circuit
     *
     * @param array $circuit
     * @return Classeur
     */
    public function setCircuit($circuit)
    {
        $this->circuit = $circuit;
    
        return $this;
    }

    /**
     * Get circuit
     *
     * @return array 
     */
    public function getCircuit()
    {
        return $this->circuit;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Classeur
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param integer $user
     * @return Classeur
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Classeur
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set validant
     *
     * @param integer $validant
     * @return Classeur
     */
    public function setValidant($validant)
    {
        $this->validant = $validant;
    
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setValidantValue() {
        $this->validant = $this->circuit[0];
    }

    /**
     * @ORM\PrePersist
     */
    public function setStatusValue() {
        $this->status = 1;
    }

    /**
     * Get validant
     *
     * @return integer 
     */
    public function getValidant()
    {
        return $this->validant;
    }

    /**
     * Set visibilite
     *
     * @param integer $visibilite
     * @return Classeur
     */
    public function setVisibilite($visibilite)
    {
        $this->visibilite = $visibilite;
    
        return $this;
    }

    /**
     * Get visibilite
     *
     * @return integer 
     */
    public function getVisibilite()
    {
        return $this->visibilite;
    }

    /**
     * Set users
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $users
     * @return Classeur
     */
    public function setUsers(\Sesile\ClasseurBundle\Entity\Classeur $users = null) {
        $this->users = $users;
        return $this;
    }

    /**
     * Get users
     *
     * @return \Sesile\ClasseurBundle\Entity\Classeur 
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param \Sesile\ClasseurBundle\Entity\ClasseurUsers $users
     * @return Classeur
     */
    public function addUser(\Sesile\ClasseurBundle\Entity\ClasseurUsers $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Sesile\ClasseurBundle\Entity\ClasseurUsers $users
     */
    public function removeUser(\Sesile\ClasseurBundle\Entity\ClasseurUsers $users)
    {
        $this->users->removeElement($users);
    }
}