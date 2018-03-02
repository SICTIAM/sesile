<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Sesile\UserBundle\Entity\User;
use Sesile\UserBundle\Entity\UserPack;

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
     * @Groups({"listClasseur", "classeurById"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="etapeClasseurs", cascade={"persist"})
     * @Groups({"classeurById", "listClasseur", "listEtapeClasseur"})
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
     * @Groups({"classeurById", "listClasseur", "listEtapeClasseur"})
     */
    private $userPacks;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     * @Groups({"classeurById"})
     *
     */
    private $ordre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="EtapeValidante", type="boolean", options={"default" = false})
     * @Groups({"classeurById", "listClasseur", "listEtapeClasseur"})
     *
     */
    private $etapeValidante = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="etapeValide", type="boolean", options={"default" = false})
     * @Groups({"classeurById", "listClasseur", "listEtapeClasseur"})
     *
     */
    private $etapeValide = 0;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", options={"default": 0})
     *
     */
    private $date;

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
        $this->userPacks = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->date = new \DateTime();
    }
    
    /**
     * Add userPacks
     *
     * @param UserPack $userPacks
     * @return EtapeClasseur
     */
    public function addUserPack(UserPack $userPacks)
    {
        $this->userPacks[] = $userPacks;
    
        return $this;
    }

    /**
     * Remove userPacks
     *
     * @param UserPack $userPacks
     */
    public function removeUserPack(UserPack $userPacks)
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
     * @param User $users
     * @return EtapeClasseur
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
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
     * @param User $userValidant
     *
     * @return EtapeClasseur
     */
    public function setUserValidant(User $userValidant = null)
    {
        $this->userValidant = $userValidant;

        return $this;
    }

    /**
     * Get userValidant
     *
     * @return User
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
     * Set date
     *
     * @param \DateTime $date
     * @return EtapeClasseur
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDateValue()
    {
        $this->date = new \DateTime();
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

    public function getValidantUsersId () {
        $usersId = array();
        foreach ($this->getUsers() as $user) {
            $usersId[] = $user->getId();
        }

        foreach ($this->getUserPacks() as $userPack) {
            foreach ($userPack->getUsers() as $user) {
                $usersId[] = $user->getId();
            }
        }

        return array_unique($usersId);

    }

}
