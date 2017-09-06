<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation as Serializer;

/**
 * TypeClasseur
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\TypeClasseurRepository")
 */
class TypeClasseur
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"getByIdCircuit"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Serializer\Groups({"getByIdCircuit"})
     */
    private $nom;


    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\Groupe", mappedBy="types", cascade={"persist"})
     * @Exclude()
     */
    private $groupes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation", type="datetime", nullable=true)
     */
    private $creation;

    /**
     * @ORM\Column(name="supprimable", type="boolean")
     * @Exclude()
     */
    private $supprimable = true;

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
     * @return TypeClasseur
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
        $this->groupes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creation = new \DateTime('now');
    }

    /**
     * Add groupes
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupes
     * @return TypeClasseur
     */
    public function addGroupe(\Sesile\UserBundle\Entity\Groupe $groupes)
    {
        $this->groupes[] = $groupes;
    
        return $this;
    }

    /**
     * Remove groupes
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupes
     */
    public function removeGroupe(\Sesile\UserBundle\Entity\Groupe $groupes)
    {
        $this->groupes->removeElement($groupes);
    }

    /**
     * Get groupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupes()
    {
        return $this->groupes;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return TypeClasseur
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
     * Set supprimable
     *
     * @param boolean $supprimable
     * @return TypeClasseur
     */
    public function setSupprimable($supprimable)
    {
        $this->supprimable = $supprimable;
    
        return $this;
    }

    /**
     * Get supprimable
     *
     * @return boolean 
     */
    public function getSupprimable()
    {
        return $this->supprimable;
    }
}