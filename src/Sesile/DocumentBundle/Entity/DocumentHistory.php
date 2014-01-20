<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentHistory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\DocumentBundle\Entity\DocumentHistoryRepository")
 */
class DocumentHistory
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
     * @ORM\Column(name="sha", type="string", length=255, nullable=true)
     */
    private $sha;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=1000)
     */
    private $comment;


    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="histories")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $document;


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
     * Set sha
     *
     * @param string $sha
     * @return DocumentHistory
     */
    public function setSha($sha)
    {
        $this->sha = $sha;

        return $this;
    }

    /**
     * Get sha
     *
     * @return string
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return DocumentHistory
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
     * Set comment
     *
     * @param string $comment
     * @return DocumentHistory
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set document
     *
     * @param \Sesile\DocumentBundle\Entity\Document $document
     * @return DocumentHistory
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