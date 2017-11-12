<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections;

/**
 * Groupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\GroupeRepository")
 */
class Groupe {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $nom;

    /**
     * @var int
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="groupes", cascade={"persist"})
     * @ORM\JoinColumn(name="collectivite", referencedColumnName="id")
     *
     */
    protected $collectivite;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\ClasseurBundle\Entity\TypeClasseur", inversedBy="groupes", cascade={"persist"})
     * @ORM\JoinTable(name="classeur_groupe")
     * @Serializer\Groups({"getByIdCircuit"})
     */
    private $types;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\EtapeGroupe", mappedBy="groupe", cascade={"persist"})
     * @ORM\OrderBy({"ordre" = "ASC"})
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $etapeGroupes;

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
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Groupe
     */
    public function setNom($nom) {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom() {
        return $this->nom;
    }

    

    /**
     * Constructor
     */
    public function __construct() {
        $this->creation = new \DateTime('now');
    }

    /**
     * Add types
     *
     * @param \Sesile\ClasseurBundle\Entity\TypeClasseur $types
     * @return Groupe
     */
    public function addType(\Sesile\ClasseurBundle\Entity\TypeClasseur $types)
    {
        $this->types[] = $types;
    
        return $this;
    }

    /**
     * Remove types
     *
     * @param \Sesile\ClasseurBundle\Entity\TypeClasseur $types
     */
    public function removeType(\Sesile\ClasseurBundle\Entity\TypeClasseur $types)
    {
        $this->types->removeElement($types);
    }

    /**
     * Get types
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Add etapeGroupes
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes
     * @return Groupe
     */
    public function addEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes)
    {
        $this->etapeGroupes[] = $etapeGroupes;
    
        return $this;
    }

    /**
     * Remove etapeGroupe
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupe
     */
    public function removeEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupe)
    {
        $this->etapeGroupes->removeElement($etapeGroupe);
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
     * @return Groupe
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
     * @return Groupe
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
}