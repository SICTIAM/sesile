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
     * @var int
     *
     * @ORM\Column(name="abscissesVisa", type="integer",nullable=true)
     */
    private $abscissesVisa;

    /**
     * @var int
     *
     * @ORM\Column(name="ordonneesVisa", type="integer", length=255,nullable=true)
     */
    private $ordonneesVisa;

    /**
     * @var int
     *
     * @ORM\Column(name="abscissesSignature", type="integer", length=255,nullable=true)
     */
    private $abscissesSignature;

    /**
     * @var int
     *
     * @ORM\Column(name="ordonneesSignature", type="integer", length=255,nullable=true)
     */
    private $ordonneesSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="couleurVisa", type="string", length=10,nullable=true)
     */
    private $couleurVisa;

    /**
     * @var string
     *
     * @ORM\Column(name="titreVisa", type="string", length=10,nullable=true,options={"default":"VISE PAR"})
     */
    private $titreVisa='VISE PAR';

    /**
     * @var int
     *
     * @ORM\Column(name="pageSignature", type="integer",nullable=true)
     */
    private $pageSignature;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\User", mappedBy="collectivite")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", mappedBy="collectivite")
     */
    private $userPacks;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\Groupe", mappedBy="collectivite")
     */
    private $groupes;

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
            $this->setTextmailnew("Un nouveau classeur {{ titre_classeur }} vient d'être déposé par {{ deposant }}
<br>
Il convient de le valider avant le {{ date_limite | date('d/m/Y') }}.
<br>
Vous pouvez visionner le classeur {{lien|raw}}");
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("Bonjour {{ deposant }},

Le classeur {{ titre_classeur }} vient d'être refusé par {{ validant }} pour le motif suivant:
<br>
{{ motif }}
<br>
Vous devez y apporter les modifications nécessaires avant de le soumettre à nouveau
<br>
Vous pouvez visionner le classeur {{lien|raw}}");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("Bonjour {{ deposant }},
<br>
Le classeur {{ titre_classeur }} vient d'être validé par {{ validant }}
<br>
Vous pouvez visionner le classeur {{lien|raw}}");
        }
    }

    private function upload() {
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
            $this->setTextmailnew('Bonjour {{ validant }},
<br><br>
Un nouveau classeur {{ titre_classeur }} vient d\'être déposé par {{ deposant }}
<br><br>
Il convient de le valider avant le {{ date_limite | date("d/m/Y") }}.
<br><br>
Vous pouvez visionner le classeur {{lien|raw}}');
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("Bonjour {{ deposant }},

Le classeur {{ titre_classeur }} vient d'être refusé par {{ validant }} pour le motif suivant:
<br>
{{ motif }}
<br>
Vous devez y apporter les modifications nécessaires avant de le soumettre à nouveau
<br>
Vous pouvez visionner le classeur {{lien|raw}}");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("Bonjour {{ deposant }},
<br><br>
Le classeur {{ titre_classeur }} vient d'être validé par {{ validant }}
<br><br>
Vous pouvez visionner le classeur {{lien|raw}}");
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

    /**
     * Add userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     * @return Collectivite
     */
    public function addUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks[] = $userPacks;
    
        return $this;
    }

    /**
     * Remove userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     */
    public function removeUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks->removeElement($userPacks);
    }

    /**
     * Get userPacks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserPacks()
    {
        return $this->userPacks;
    }

    /**
     * Add groupes
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupes
     * @return Collectivite
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
     * Set abscissesVisa
     *
     * @param integer $abscissesVisa
     * @return Collectivite
     */
    public function setAbscissesVisa($abscissesVisa)
    {
        $this->abscissesVisa = $abscissesVisa;
    
        return $this;
    }

    /**
     * Get abscissesVisa
     *
     * @return integer 
     */
    public function getAbscissesVisa()
    {
        return $this->abscissesVisa;
    }

    /**
     * Set ordonneesVisa
     *
     * @param integer $ordonneesVisa
     * @return Collectivite
     */
    public function setOrdonneesVisa($ordonneesVisa)
    {
        $this->ordonneesVisa = $ordonneesVisa;
    
        return $this;
    }

    /**
     * Get ordonneesVisa
     *
     * @return integer 
     */
    public function getOrdonneesVisa()
    {
        return $this->ordonneesVisa;
    }

    /**
     * Set abscissesSignature
     *
     * @param integer $abscissesSignature
     * @return Collectivite
     */
    public function setAbscissesSignature($abscissesSignature)
    {
        $this->abscissesSignature = $abscissesSignature;
    
        return $this;
    }

    /**
     * Get abscissesSignature
     *
     * @return integer 
     */
    public function getAbscissesSignature()
    {
        return $this->abscissesSignature;
    }

    /**
     * Set ordonneesSignature
     *
     * @param integer $ordonneesSignature
     * @return Collectivite
     */
    public function setOrdonneesSignature($ordonneesSignature)
    {
        $this->ordonneesSignature = $ordonneesSignature;
    
        return $this;
    }

    /**
     * Get ordonneesSignature
     *
     * @return integer 
     */
    public function getOrdonneesSignature()
    {
        return $this->ordonneesSignature;
    }

    /**
     * Set couleurVisa
     *
     * @param string $couleurVisa
     * @return Collectivite
     */
    public function setCouleurVisa($couleurVisa)
    {
        $this->couleurVisa = $couleurVisa;
    
        return $this;
    }

    /**
     * Get couleurVisa
     *
     * @return string 
     */
    public function getCouleurVisa()
    {
        return $this->couleurVisa;
    }

    /**
     * Set titreVisa
     *
     * @param string $titreVisa
     * @return Collectivite
     */
    public function setTitreVisa($titreVisa)
    {
        $this->titreVisa = $titreVisa;
    
        return $this;
    }

    /**
     * Get titreVisa
     *
     * @return string 
     */
    public function getTitreVisa()
    {
        return $this->titreVisa;
    }

    /**
     * Set pageSignature
     *
     * @param integer $pageSignature
     * @return Collectivite
     */
    public function setPageSignature($pageSignature)
    {
        $this->pageSignature = $pageSignature;
    
        return $this;
    }

    /**
     * Get pageSignature
     *
     * @return integer 
     */
    public function getPageSignature()
    {
        return $this->pageSignature;
    }
}