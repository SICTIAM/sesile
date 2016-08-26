<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sesile\ClasseurBundle\Entity\Classeur;
use Imagick;

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
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     *
     */
    private $token;

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

    /**
     * @param int $page
     * @param string $format
     * @return bool|string
     */
    public function getPDFImage($page = 0, $orientation = "PORTRAIT") {
        if($this->getType() == "application/pdf") {


            $imagick = new Imagick();
//            $imagick->readImage('mytest.pdf');

            $imagick->readImage('uploads/docs/' . $this->getRepourl() . '[' . $page . ']');

            // Si le PDF est au format portrait
            if ($orientation == "PORTRAIT") {
                $imagick->thumbnailImage(210,297,true,true);
            } else {
                $imagick->thumbnailImage(297,210,true,true);
            }
            $imagick->setFormat('jpg');
//          $thumb = $imagick->getImageBlob();
//          header("Content-Type: image/jpg");
            return base64_encode($imagick->getImageBlob());

            /*$imagick->writeImage('uploads/docs/' . $doc->getRepourl() . '.output.jpg');
            $thumbail = 'uploads/docs/' . $doc->getRepourl() . '.output.jpg';*/
        } else return true;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Document
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
}