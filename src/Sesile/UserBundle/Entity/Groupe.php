<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\GroupeRepository")
 */
class Groupe
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
     * @ORM\Column(name="Nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Collectivite", type="string", length=255)
     */
    private $collectivite;


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
     * @return Groupe
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
     * Set collectivite
     *
     * @param string $collectivite
     * @return Groupe
     */
    public function setCollectivite($collectivite)
    {
        $this->collectivite = $collectivite;

        return $this;
    }

    /**
     * Get collectivite
     *
     * @return string
     */
    public function getCollectivite()
    {
        return $this->collectivite;
    }

    /**
     * @ORM\PrePersist()
     *
     */
    public function preUpload()
    {

        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique

            $this->path = sha1(uniqid(mt_rand(), true)) . '.' . $this->file->guessExtension();
        }
    }

    /**
     *
     *
     */
    public function upload($Dirpath)
    {
        if (null === $this->file) {
            return;
        }


        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        //   var_dump($this->getUploadDir());var_dump($this->file->getClientOriginalName());exit;
        $this->file->move($Dirpath, $this->path);


        unset($this->file);
    }

    /**
     *
     */
    public function removeUpload($Dirpath)
    {

        if ($file = $Dirpath . $this->path) {
            unlink($file);
        }
    }
}