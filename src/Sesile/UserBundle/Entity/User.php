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
     * @ORM\Column(name="Prenom", type="string", length=255)
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
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
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
     * @ORM\PreUpdate()
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
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
}
