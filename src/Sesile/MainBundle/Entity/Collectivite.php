<?php

namespace Sesile\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Collectivite
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Sesile\MainBundle\Entity\CollectiviteRepository")
 */
class Collectivite
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
     * @ORM\Column(name="domain", type="string", length=255)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @Assert\Image(
     *      mimeTypesMessage = "Ce fichier n'est pas une image",
     *      maxSize = "5M",
     *      maxSizeMessage = "Too big."
     *      )
     */
    private $file;


    /**
     * @var text
     *
     * @ORM\Column(name="textmailnew", type="string", length=3000, nullable=true)
     */
    private $textmailnew;

    /**
     * @var text
     *
     * @ORM\Column(name="textmailrefuse", type="string", length=3000, nullable=true)
     */
    private $textmailrefuse;

    /**
     * @var text
     *
     * @ORM\Column(name="textmailwalid", type="string", length=3000, nullable=true)
     */
    private $textmailwalid;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\User", mappedBy="collectivite")
     */
    private $users;


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
     * @return Collectivite
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
     * Set domain
     *
     * @param string $domain
     * @return Collectivite
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Collectivite
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Collectivite
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Collectivite
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }


    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->image ? null : $this->getUploadRootDir() . $this->image;
    }

    public function getWebPath()
    {
        return null === $this->image ? null : $this->getUploadDir() . $this->image;
    }

    private function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        $controller = new Controller();
        /*$upload = $controller->setContainer()getParameter('upload');*/
        return "uploads/logo_coll/";
    }

    /**
     * @ORM\PrePersist()
     *
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique

            $this->image = sha1(uniqid(mt_rand(), true)) . '.' . $this->file->guessExtension();
            $this->upload($this->getUploadRootDir());
        }


        if ($this->getTextmailnew() == null) {
            $this->setTextmailnew("Un nouveau classeur vient d'être déposé par {{ deposant }}
                                    <br>
                                        {{ titre_classeur }}
                                        <br>
                                    Il devra être validé avant le {{ date_limite | date('d/m/Y') }}..

                            Lien vers le classeur {{ lien }})");
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("Le classeur \"{{ titre_classeur }}\" vient d'être refusé par {{ validant }}
                <br>
                Il devra être corrigé et validé avant le {{ date_limite | date('d/m/Y') }}..<br>

                Lien vers le classeur {{ lien }}");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("Un nouveau classeur vient d'être validé par {{ validant }}
                <br>
                {{ titre_classeur }}
                <br>
                Il devra être validé avant le {{ date_limite | date('d/m/Y') }}..<br>

                Lien vers le classeur {{ lien }}");
        }
    }

    private function upload()
    {
        if (null === $this->file) {
            return;
        }
        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        if (!file_exists($this->getUploadRootDir())) {
            mkdir($this->getUploadRootDir());
        }
        $this->file->move($this->getUploadRootDir(), $this->image);


        unset($this->file);
    }

    public function removeUpload()
    {
        if ($file = $this->getUploadRootDir() . $this->image) {
            if(file_exists($file) && !is_dir($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Set textmailnew
     *
     * @param string $textmailnew
     * @return Collectivite
     */
    public function setTextmailnew($textmailnew)
    {
        $this->textmailnew = $textmailnew;

        return $this;
    }

    /**
     * Get textmailnew
     *
     * @return string
     */
    public function getTextmailnew()
    {
        return $this->textmailnew;
    }

    /**
     * Set textmailrefuse
     *
     * @param string $textmailrefuse
     * @return Collectivite
     */
    public function setTextmailrefuse($textmailrefuse)
    {
        $this->textmailrefuse = $textmailrefuse;

        return $this;
    }

    /**
     * Get textmailrefuse
     *
     * @return string
     */
    public function getTextmailrefuse()
    {
        return $this->textmailrefuse;
    }

    /**
     * Set textmailwalid
     *
     * @param string $textmailwalid
     * @return Collectivite
     */
    public function setTextmailwalid($textmailwalid)
    {
        $this->textmailwalid = $textmailwalid;

        return $this;
    }

    /**
     * Get textmailwalid
     *
     * @return string
     */
    public function getTextmailwalid()
    {
        return $this->textmailwalid;
    }

    /**
     * Constructor
     */
    public function __construct()
    {

        if ($this->getTextmailnew() == null) {
            $this->setTextmailnew("Un nouveau classeur vient d'être déposé par {{ deposant }}
                                    <br>
                                        {{ titre_classeur }}
                                        <br>
                                    Il devra être validé avant le {{ date_limite | date('d/m/Y') }}..

                            Lien vers le classeur {{ lien }})");
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("Le classeur \"{{ titre_classeur }}\" vient d'être refusé par {{ validant }}
                <br>
                Il devra être corrigé et validé avant le {{ date_limite | date('d/m/Y') }}..<br>

                Lien vers le classeur {{ lien }}");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("Un nouveau classeur vient d'être validé par {{ validant }}
                <br>
                {{ titre_classeur }}
                <br>
                Il devra être validé avant le {{ date_limite | date('d/m/Y') }}..<br>

                Lien vers le classeur {{ lien }}");
        }
    }

    /**
     * Add users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     * @return Collectivite
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
}