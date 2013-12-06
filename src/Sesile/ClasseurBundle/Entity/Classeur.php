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
     * @var array
     *
     * @ORM\Column(name="circuit", type="array")
     */
    private $circuit;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;



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
}