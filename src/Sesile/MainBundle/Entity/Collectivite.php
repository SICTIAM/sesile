<?php

namespace Sesile\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\Mapping\Annotation as Gedmo;


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
     * @Serializer\Groups({"currentUser",
     *                      "getAllCollectivite",
     *                      "getCollectiviteById",
     *                      "listUsers",
     *                      "UserId",
     *                      "listClasseur",
     *                      "classeurById",
     *     })
     */
    private $id;

    /**
     * @Serializer\Groups({"getCollectiviteById"})
     * @ORM\OneToOne(targetEntity="Sesile\MainBundle\Entity\CollectiviteOzwillo", mappedBy="collectivite", cascade={"persist"})
     *
     */
    private $ozwillo;

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=10, nullable=true)
     * @Serializer\Groups({"getCollectiviteById"
     * })
     */
    private $siren;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Serializer\Groups({"currentUser",
     *                      "getAllCollectivite",
     *                      "getCollectiviteById",
     *                      "listUsers",
     *                      "UserId"
     * })
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=255)
     * @Gedmo\Slug(fields={"nom"}, separator="-", updatable=false, unique=false)
     * @Serializer\Groups({"currentUser",
     *                      "getAllCollectivite",
     *                      "getCollectiviteById"})
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Serializer\Groups({"getAllCollectivite","getCollectiviteById"})
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $message;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     * @Serializer\Groups({"getAllCollectivite", "getCollectiviteById"})
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
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $textmailnew;

    /**
     * @var text
     *
     * @ORM\Column(name="textmailrefuse", type="string", length=3000, nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $textmailrefuse;

    /**
     * @var text
     *
     * @ORM\Column(name="textmailwalid", type="string", length=3000, nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $textmailwalid;

    /**
     * @var text
     *
     * @ORM\Column(name="textcopymailnew", type="string", length=3000, nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $textcopymailnew;

    /**
     * @var text
     *
     * @ORM\Column(name="textcopymailwalid", type="string", length=3000, nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $textcopymailwalid;

    /**
     * @var int
     *
     * @ORM\Column(name="abscissesVisa", type="integer",nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $abscissesVisa = 135;

    /**
     * @var int
     *
     * @ORM\Column(name="ordonneesVisa", type="integer", length=255,nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $ordonneesVisa = 11;

    /**
     * @var int
     *
     * @ORM\Column(name="abscissesSignature", type="integer", length=255,nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $abscissesSignature = 123;

    /**
     * @var int
     *
     * @ORM\Column(name="ordonneesSignature", type="integer", length=255,nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $ordonneesSignature = 253;

    /**
     * @var string
     *
     * @ORM\Column(name="couleurVisa", type="string", length=10,nullable=true)
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $couleurVisa = "#454545";

    /**
     * @var string
     *
     * @ORM\Column(name="titreVisa", type="string", length=250,nullable=true,options={"default":"VISE PAR"})
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $titreVisa = "VISE PAR";

    /**
     * @var int
     *
     * @ORM\Column(name="pageSignature", type="integer", options={"default"=0})
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $pageSignature = 0;

    /**
     * @var \Doctrine\Common\Collections\Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", mappedBy="collectivities")
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
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\TypeClasseur", mappedBy="collectivites")
     * @ORM\OrderBy({"nom": "ASC"})
     * @Serializer\Exclude()
     */
    private $types;

    /**
     * @var int
     *
     * @Assert\Range(
     *     min = 10,
     *     max = 365,
     *     minMessage = "Le minimum est {{ limit }} jours",
     *     maxMessage = "le maximum est {{ limit }} jours",
     *     payload = {"severity" = "error"}
     * )
     * @ORM\Column(name="deleteClasseurAfter", type="integer", options={"default"=180})
     * @Serializer\Groups({"getCollectiviteById"})
     */
    private $deleteClasseurAfter = 180;

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
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
        // $controller = new Controller();
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
            $this->setTextmailnew("<p>Bonjour {{ validant }},<br /><br />Un nouveau classeur <strong>{{ titre_classeur }}</strong> vient d'&ecirc;tre d&eacute;pos&eacute; par {{ deposant }}<br /><br />Il convient de le valider avant le <strong>{{ date_limite | date('d/m/Y') }}</strong>.<br /><br />Vous pouvez visionner le classeur {{lien|raw}}</p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("<p>Bonjour {{ deposant }}, <br /><br />Le classeur {{ titre_classeur }} vient d'&ecirc;tre refus&eacute; par {{ validant }} pour le motif suivant: <br />{{ motif }} <br /><br />Vous devez y apporter les modifications n&eacute;cessaires avant de le soumettre &agrave; nouveau <br />Vous pouvez visionner le classeur <strong>{{lien|raw}}</strong></p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("<p>Bonjour {{ deposant }},<br /><br />Le classeur \"{{ titre_classeur }}\" vient d'&ecirc;tre <strong>valid&eacute;</strong> par {{ validant }}<br /><br />Vous pouvez visionner le classeur {{lien|raw}}<br /><br /></p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getMessage() == null) {
            $this->setMessage("<p>Le parapheur &eacute;lectronique S.E.SI.LE, <strong>Syst&egrave;me Electronique de SIgnature LEgale</strong>, vous offre l&rsquo;opportunit&eacute; de d&eacute;poser vos fichiers afin de proc&eacute;der &agrave; leur validation selon des circuits d&eacute;finis mais &eacute;galement de les <strong>signer &eacute;lectroniquement</strong>.</p><p>SESILE comporte g&eacute;n&eacute;ralement les fonctionnalit&eacute;s suivantes (par ordre d'utilisation dans un flux documentaire)</p><ul>
<li>cr&eacute;ation d'un objet \"document\", soit par import manuel, soit en sortie d'un logiciel produisant ce document</li>
<li>d&eacute;finition de <a href='https://fr.wikipedia.org/wiki/M%C3%A9tadonn%C3%A9e'>m&eacute;tadonn&eacute;es</a> pour cet objet</li>
<li>choix (et param&eacute;trage) d'un circuit de validation/visas et de signature(s)</li>
<li>envoi dans ce circuit, notification des intervenants dans le circuit</li>
<li>validations ou refus</li>
<li>suivi permanent de l'&eacute;tat d'avancement et acc&egrave;s &agrave; l'historique de traitement</li>
<li><a href='https://fr.wikipedia.org/wiki/Signature_num%C3%A9rique'>signature &eacute;lectronique</a>, gr&acirc;ce &agrave; un <a href='https://fr.wikipedia.org/wiki/Certificat_%C3%A9lectronique'>certificat &eacute;lectronique</a></li>
<li>stockage de l'objet (document, preuve de signature, m&eacute;tadonn&eacute;es et historique de traitement) ou export vers un logiciel tiers</li>
</ul>");
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

    public function removeUpload($file)
    {
        if(file_exists($file) && !is_dir($file)) {
            unlink($file);
            $this->setImage(null);
            return $this;
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
            $this->setTextmailnew("<p>Bonjour {{ validant }},<br /><br />Un nouveau classeur <strong>{{ titre_classeur }}</strong> vient d'&ecirc;tre d&eacute;pos&eacute; par {{ deposant }}<br /><br />Il convient de le valider avant le <strong>{{ date_limite | date('d/m/Y') }}</strong>.<br /><br />Vous pouvez visionner le classeur {{lien|raw}}</p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getTextmailrefuse() == null) {
            $this->setTextmailrefuse("<p>Bonjour {{ deposant }}, <br /><br />Le classeur {{ titre_classeur }} vient d'&ecirc;tre refus&eacute; par {{ validant }} pour le motif suivant: <br />{{ motif }} <br /><br />Vous devez y apporter les modifications n&eacute;cessaires avant de le soumettre &agrave; nouveau <br />Vous pouvez visionner le classeur <strong>{{lien|raw}}</strong></p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getTextmailwalid() == null) {
            $this->setTextmailwalid("<p>Bonjour {{ deposant }},<br /><br />Le classeur \"{{ titre_classeur }}\" vient d'&ecirc;tre <strong>valid&eacute;</strong> par {{ validant }}<br /><br />Vous pouvez visionner le classeur {{lien|raw}}<br /><br /></p><table><tbody><tr><td>**logo_coll**</td><td>{{ qualite }}<br />{{ validant }}</td></tr></tbody></table>");
        }

        if ($this->getMessage() == null) {
            $this->setMessage("<p>Le parapheur &eacute;lectronique S.E.SI.LE, <strong>Syst&egrave;me Electronique de SIgnature LEgale</strong>, vous offre l&rsquo;opportunit&eacute; de d&eacute;poser vos fichiers afin de proc&eacute;der &agrave; leur validation selon des circuits d&eacute;finis mais &eacute;galement de les <strong>signer &eacute;lectroniquement</strong>.</p><p>SESILE comporte g&eacute;n&eacute;ralement les fonctionnalit&eacute;s suivantes (par ordre d'utilisation dans un flux documentaire)</p><ul>
<li>cr&eacute;ation d'un objet \"document\", soit par import manuel, soit en sortie d'un logiciel produisant ce document</li>
<li>d&eacute;finition de <a href='https://fr.wikipedia.org/wiki/M%C3%A9tadonn%C3%A9e'>m&eacute;tadonn&eacute;es</a> pour cet objet</li>
<li>choix (et param&eacute;trage) d'un circuit de validation/visas et de signature(s)</li>
<li>envoi dans ce circuit, notification des intervenants dans le circuit</li>
<li>validations ou refus</li>
<li>suivi permanent de l'&eacute;tat d'avancement et acc&egrave;s &agrave; l'historique de traitement</li>
<li><a href='https://fr.wikipedia.org/wiki/Signature_num%C3%A9rique'>signature &eacute;lectronique</a>, gr&acirc;ce &agrave; un <a href='https://fr.wikipedia.org/wiki/Certificat_%C3%A9lectronique'>certificat &eacute;lectronique</a></li>
<li>stockage de l'objet (document, preuve de signature, m&eacute;tadonn&eacute;es et historique de traitement) ou export vers un logiciel tiers</li>
</ul>");
        }

        if ($this->getTextcopymailnew() === null) {
            $this->setTextcopymailnew("<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être déposé par {{ deposant }}, pour validation à {{ validant }}, à la date du <strong>{{ date_limite | date('d/m/Y') }}.</strong></p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>");
        }

        if ($this->getTextcopymailwalid() === null) {
            $this->setTextcopymailwalid("<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être validé par {{ validant }}.</p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>");
        }
        $this->users = new ArrayCollection();
    }

    /**
     * Add users
     *
     * @param User $user
     * @return Collectivite
     */
    public function addUser(\Sesile\UserBundle\Entity\User $user)
    {
        if ($this->users->contains($user)) {
            return;
        }
        $this->users->add($user);
        $user->addCollectivity($this);

        return $this;
    }

    /**
     *
     */
    public function clearAllUsers()
    {
        $this->users->clear();
    }

    /**
     * Remove users
     *
     * @param \Sesile\UserBundle\Entity\User $user
     */
    public function removeUser(\Sesile\UserBundle\Entity\User $user)
    {
        if (!$this->users->contains($user)) {
            return;
        }
        $this->users->removeElement($user);
        $user->removeCollectivity($this);
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

    /**
     * Set deleteClasseurAfter
     *
     * @param integer $deleteClasseurAfter
     *
     * @return Collectivite
     */
    public function setDeleteClasseurAfter($deleteClasseurAfter)
    {
        $this->deleteClasseurAfter = $deleteClasseurAfter;

        return $this;
    }

    /**
     * Get deleteClasseurAfter
     *
     * @return integer
     */
    public function getDeleteClasseurAfter()
    {
        return $this->deleteClasseurAfter;
    }

    /**
     * Add type
     *
     * @param \Sesile\ClasseurBundle\Entity\TypeClasseur $type
     *
     * @return Collectivite
     */
    public function addType(\Sesile\ClasseurBundle\Entity\TypeClasseur $type)
    {
        $this->types[] = $type;

        return $this;
    }

    /**
     * Remove type
     *
     * @param \Sesile\ClasseurBundle\Entity\TypeClasseur $type
     */
    public function removeType(\Sesile\ClasseurBundle\Entity\TypeClasseur $type)
    {
        $this->types->removeElement($type);
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
     * Set ozwillo
     *
     * @param \Sesile\MainBundle\Entity\CollectiviteOzwillo $ozwillo
     *
     * @return Collectivite
     */
    public function setOzwillo(\Sesile\MainBundle\Entity\CollectiviteOzwillo $ozwillo = null)
    {
        $this->ozwillo = $ozwillo;

        return $this;
    }

    /**
     * Get ozwillo
     *
     * @return \Sesile\MainBundle\Entity\CollectiviteOzwillo
     */
    public function getOzwillo()
    {
        return $this->ozwillo;
    }

    /**
     * Set siren
     *
     * @param string $siren
     *
     * @return Collectivite
     */
    public function setSiren($siren)
    {
        $this->siren = $siren;

        return $this;
    }

    /**
     * Get siren
     *
     * @return string
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * Set textcopymailnew
     *
     * @param string $textcopymailnew
     *
     * @return Collectivite
     */
    public function setTextcopymailnew($textcopymailnew)
    {
        $this->textcopymailnew = $textcopymailnew;

        return $this;
    }

    /**
     * Get textcopymailnew
     *
     * @return string
     */
    public function getTextcopymailnew()
    {
        return $this->textcopymailnew;
    }

    /**
     * Set textcopymailwalid
     *
     * @param string $textcopymailwalid
     *
     * @return Collectivite
     */
    public function setTextcopymailwalid($textcopymailwalid)
    {
        $this->textcopymailwalid = $textcopymailwalid;

        return $this;
    }

    /**
     * Get textcopymailwalid
     *
     * @return string
     */
    public function getTextcopymailwalid()
    {
        return $this->textcopymailwalid;
    }
}
