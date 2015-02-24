<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sesile\ClasseurBundle\Entity\Classeur;


/**
 * Document
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\DocumentBundle\Entity\DocumentRepository")
 *
 * 
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *  
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *  
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="repourl", type="string", length=1000)
     */
    private $repourl;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     *  
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="signed", type="boolean")
     *  
     */
    private $signed;

    /**
     * @ORM\OneToMany(targetEntity="DocumentHistory", mappedBy="document")
     *  
     */
    protected $histories;


    /**
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="documents")
     * @ORM\JoinColumn(name="classeur_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $classeur;


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
     * Set name
     *
     * @param string $name
     * @return Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set repourl
     *
     * @param string $repourl
     * @return Document
     */
    public function setRepourl($repourl)
    {
        $this->repourl = $repourl;

        return $this;
    }

    /**
     * Get repourl
     *
     * @return string
     */
    public function getRepourl()
    {
        return $this->repourl;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Document
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
     * Set signed
     *
     * @param boolean $signed
     * @return Document
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * Get signed
     *
     * @return boolean
     */
    public function getSigned()
    {
        return $this->signed;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->histories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add histories
     *
     * @param \Sesile\DocumentBundle\Entity\DocumentHistory $histories
     * @return Document
     */
    public function addHistorie(\Sesile\DocumentBundle\Entity\DocumentHistory $histories)
    {
        $this->histories[] = $histories;

        return $this;
    }

    /**
     * Remove histories
     *
     * @param \Sesile\DocumentBundle\Entity\DocumentHistory $histories
     */
    public function removeHistorie(\Sesile\DocumentBundle\Entity\DocumentHistory $histories)
    {
        $this->histories->removeElement($histories);
    }

    /**
     * Get histories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHistories()
    {
        return $this->histories;
    }

    /**
     * Set classeur
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeur
     * @return Document
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
}