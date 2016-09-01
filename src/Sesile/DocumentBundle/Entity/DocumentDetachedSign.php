<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentDetachedSign
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\DocumentBundle\Entity\DocumentDetachedSignRepository")
 *
 *
 */
class DocumentDetachedSign
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
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="detachedsign")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $document;

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
     * @ORM\Column(name="repourl", type="string", length=150)
     *
     */
    private $repourl;


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
     * @return DocumentDetachedSign
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
     * @return DocumentDetachedSign
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
     * Set document
     *
     * @param \Sesile\DocumentBundle\Entity\Document $document
     * @return DocumentDetachedSign
     */
    public function setDocument(\Sesile\DocumentBundle\Entity\Document $document = null)
    {
        $this->document = $document;
    
        return $this;
    }

    /**
     * Get document
     *
     * @return \Sesile\DocumentBundle\Entity\Document 
     */
    public function getDocument()
    {
        return $this->document;
    }
}