<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Sesile\ClasseurBundle\Entity\Classeur;
use \Imagick;

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
     * @Groups({"classeurById", "listClasseur"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"classeurById", "listClasseur"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="repourl", type="string", length=1000)
     * @Groups({"classeurById", "listClasseur"})
     */
    private $repourl;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @Groups({"classeurById", "listClasseur"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     * @Groups({"classeurById", "listClasseur"})
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="signed", type="boolean")
     * @Groups({"classeurById"})
     *  
     */
    private $signed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="downloaded", type="boolean")
     *
     */
    private $downloaded = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="display", type="boolean", nullable=true)
     *
     */
    private $display = true;

    /**
     * @ORM\OneToMany(targetEntity="DocumentHistory", mappedBy="document")
     *  
     */
    protected $histories;

    /**
     * @ORM\OneToMany(targetEntity="DocumentDetachedSign", mappedBy="document")
     *
     */
    protected $detachedsign;

    /**
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="documents")
     * @ORM\JoinColumn(name="classeur_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $classeur;

    /**
     * @var integer
     * @Groups({"classeurById"})
     */
    protected $size;


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
     * Get short name
     *
     * @return string
     */
    public function getShortName()
    {
        $nbMaxCharacters = 40;
        $name = $this->name;
        $extension = explode('.', $name);
        $extension = strtolower(end($extension));
        if (strlen($name) > $nbMaxCharacters) {
            return substr($name, 0, $nbMaxCharacters) . '....' . $extension;
        }
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
        $this->token = hash('md5', random_bytes(10));
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
     * @param int $format
     * @param string $path
     * @return true|array
     * @throws \ImagickException
     */
    public function getPDFImage($page = 0, $format, $path) {
        if($this->getType() == "application/pdf") {
            $imagick = new Imagick();
            $img = new Imagick($path . $this->getRepourl());
            $nbr_page = $img->getNumberImages() - 1;
            $imagick->readImage($path . $this->getRepourl() . '[' . $page . ']');
            $imagicklast = new Imagick($path . $this->getRepourl() . '[' . $nbr_page . ']');

            // Si le PDF est au format portrait
            if ($format == "PORTRAIT") {
                $imagick->thumbnailImage(210,297,true,true);
                $imagicklast->thumbnailImage(210,297,true,true);
            } else {
                $imagick->thumbnailImage(297,210,true,true);
                $imagicklast->thumbnailImage(297,210,true,true);
            }
            $imagick->setFormat('jpg');
            $imagicklast->setFormat('jpg');
//          $thumb = $imagick->getImageBlob();
//          header("Content-Type: image/jpg");
            return array(base64_encode($imagick->getImageBlob()), base64_encode($imagicklast->getImageBlob()));
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

    /**
     * Set display
     *
     * @param boolean $display
     * @return Document
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    
        return $this;
    }

    /**
     * Get display
     *
     * @return boolean 
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Add detachedsign
     *
     * @param \Sesile\DocumentBundle\Entity\DocumentDetachedSign $detachedsign
     * @return Document
     */
    public function addDetachedsign(\Sesile\DocumentBundle\Entity\DocumentDetachedSign $detachedsign)
    {
        $this->detachedsign[] = $detachedsign;
    
        return $this;
    }

    /**
     * Remove detachedsign
     *
     * @param \Sesile\DocumentBundle\Entity\DocumentDetachedSign $detachedsign
     */
    public function removeDetachedsign(\Sesile\DocumentBundle\Entity\DocumentDetachedSign $detachedsign)
    {
        $this->detachedsign->removeElement($detachedsign);
    }

    /**
     * Get detachedsign
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetachedsign()
    {
        return $this->detachedsign;
    }

    /**
     * Set downloaded
     *
     * @param boolean $downloaded
     *
     * @return Document
     */
    public function setDownloaded($downloaded)
    {
        if ($this->getClasseur()->getStatus() == 2 && $downloaded == true) {
            $this->downloaded = true;
        } else {
            $this->downloaded = false;
        }

        return $this;
    }

    /**
     * Get downloaded
     *
     * @return boolean
     */
    public function getDownloaded()
    {
        return $this->downloaded;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size)
    {
        $this->size = $size;
    }
}