<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=255)
     */
    protected $Nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Prenom", type="string", length=255, nullable=true)
     */
    protected $Prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\Image(
     *      mimeTypesMessage = "Ce fichier n'est pas une image",
     *      maxSize = "5M",
     *      maxSizeMessage = "Too big."
     *      )
     */
    protected $file;


    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    protected $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=6, nullable=true)
     */
    protected $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=255, nullable=true)
     */
    protected $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="departement", type="string", length=255, nullable=true)
     */
    protected $departement;


    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=true)
     */
    protected $role;


    public function setPath($path)
    {
        return $this->path = $path;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . $this->path;
    }

    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return '/home/sesile/web/images/avatars';
    }

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="user")
     */
    protected $delegationsRecues;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="delegant")
     */
    protected $delegationsDonnees;

    public function setPrenom($Prenom)
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function setNom($Nom)
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * renvoie le nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->Nom;
    }


    /**
     * renvoie le prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->Prenom;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }


    public function getCredentialsExpireAt()
    {
        return $this->getCredentialsExpireAt();
    }

    /**
     * @ORM\PrePersist()
     *
     */
    public function preUpload()
    {

        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique
            $this->path = "/images/avatars/" . sha1(uniqid(mt_rand(), true)) . '.' . $this->file->guessExtension();
        }
    }

    /**
     *
     *
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }


        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        //   var_dump($this->getUploadDir());var_dump($this->file->getClientOriginalName());exit;
        $this->file->move($this->getUploadDir(), $this->path);


        unset($this->file);
    }

    /**
     *
     */
    public function removeUpload()
    {
        if ($file = "/home/sesile/web" . $this->path) {
            unlink($file);
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {

        parent::__construct();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Add delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     * @return User
     */
    public function addDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues)
    {
        $this->delegationsRecues[] = $delegationsRecues;

        return $this;
    }

    /**
     * Remove delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     */
    public function removeDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues)
    {
        $this->delegationsRecues->removeElement($delegationsRecues);
    }

    /**
     * Get delegationsRecues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDelegationsRecues()
    {
        return $this->delegationsRecues;
    }

    /**
     * Add delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     * @return User
     */
    public function addDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees[] = $delegationsDonnees;

        return $this;
    }

    /**
     * Remove delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     */
    public function removeDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees->removeElement($delegationsDonnees);
    }

    /**
     * Get delegationsDonnees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDelegationsDonnees()
    {
        return $this->delegationsDonnees;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return User
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return User
     */
    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get cp
     *
     * @return string
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return User
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set departement
     *
     * @param string $departement
     * @return User
     */
    public function setDepartement($departement)
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * Get departement
     *
     * @return string
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}