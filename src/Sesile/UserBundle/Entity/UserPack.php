<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserPack
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserPackRepository")
 */
class UserPack
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
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="userPacks", cascade={"persist"})
     */
    private $users;


    /***
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="userPacks", cascade={"persist"})
     */
    private $etapeClasseurs;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeGroupe", mappedBy="userPacks", cascade={"persist"})
     */
    private $etapeGroupes;


    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="$userPacks")
     * @ORM\JoinColumn(name="collectivite", referencedColumnName="id")
     *
     */
    protected $collectivite;


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
     * @return UserPack
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
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->etapeGroupes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     * @return UserPack
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
     * Add etapeGroupes
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes
     * @return UserPack
     */
    public function addEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes)
    {
        $this->etapeGroupes[] = $etapeGroupes;
    
        return $this;
    }

    /**
     * Remove etapeGroupes
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes
     */
    public function removeEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes)
    {
        $this->etapeGroupes->removeElement($etapeGroupes);
    }

    /**
     * Get etapeGroupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtapeGroupes()
    {
        return $this->etapeGroupes;
    }

    /**
     * Set collectivite
     *
     * @param \Sesile\MainBundle\Entity\Collectivite $collectivite
     * @return UserPack
     */
    public function setCollectivite(\Sesile\MainBundle\Entity\Collectivite $collectivite = null)
    {
        $this->collectivite = $collectivite;
    
        return $this;
    }

    /**
     * Get collectivite
     *
     * @return \Sesile\MainBundle\Entity\Collectivite 
     */
    public function getCollectivite()
    {
        return $this->collectivite;
    }
}