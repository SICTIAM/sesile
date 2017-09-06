<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
     * @Serializer\Groups({"listEtapeClasseur","listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Serializer\Groups({"listEtapeClasseur","listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $nom;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="userPacks", cascade={"persist"})
     * @Serializer\Groups({"listEtapeClasseur"})
     */
    private $users;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="userPacks", cascade={"persist"})
     */
    private $etapeClasseurs;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeGroupe", mappedBy="userPacks", cascade={"persist"})
     */
    private $etapeGroupesUP;


    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="userPacks")
     * @ORM\JoinColumn(name="collectivite", referencedColumnName="id")
     * @Serializer\Exclude()
     *
     */
    protected $collectivite;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation", type="datetime", nullable=true)
     */
    private $creation;


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
        $this->etapeGroupesUP = new \Doctrine\Common\Collections\ArrayCollection();
        $this->etapeClasseurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creation = new \DateTime('now');
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

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return UserPack
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
     * Add etapeClasseurs
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs
     * @return UserPack
     */
    public function addEtapeClasseur(\Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs)
    {
        $this->etapeClasseurs[] = $etapeClasseurs;
    
        return $this;
    }

    /**
     * Remove etapeClasseurs
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs
     */
    public function removeEtapeClasseur(\Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs)
    {
        $this->etapeClasseurs->removeElement($etapeClasseurs);
    }

    /**
     * Get etapeClasseurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtapeClasseurs()
    {
        return $this->etapeClasseurs;
    }

    /**
     * Add etapeGroupesUP
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupesUP
     * @return UserPack
     */
    public function addEtapeGroupesUP(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupesUP)
    {
        $this->etapeGroupesUP[] = $etapeGroupesUP;
    
        return $this;
    }

    /**
     * Remove etapeGroupesUP
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupesUP
     */
    public function removeEtapeGroupesUP(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupesUP)
    {
        $this->etapeGroupesUP->removeElement($etapeGroupesUP);
    }

    /**
     * Get etapeGroupesUP
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtapeGroupesUP()
    {
        return $this->etapeGroupesUP;
    }
}