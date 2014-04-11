<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\GroupeRepository")
 */
class Groupe {
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
     * @ORM\Column(name="collectivite", type="string", length=255)
     */
    private $collectivite;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\UserGroupe", mappedBy="groupe")
     */
    private $hierarchie;

    /**
     * @var string
     * @ORM\Column(name="couleur", type="string", length=255)
     */
    private $couleur;

    /**
     * @var int
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="json", type="text")
     */
    private $json;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Groupe
     */
    public function setNom($nom) {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set collectivite
     *
     * @param string $collectivite
     * @return Groupe
     */
    public function setCollectivite($collectivite) {
        $this->collectivite = $collectivite;
        return $this;
    }

    /**
     * Get collectivite
     *
     * @return string
     */
    public function getCollectivite() {
        return $this->collectivite;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->hierarchie = new \Doctrine\Common\Collections\ArrayCollection();
    }
    


    /**
     * Set couleur
     *
     * @param string $couleur
     * @return Groupe
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;
    
        return $this;
    }

    /**
     * Get couleur
     *
     * @return string
     */
    public function getCouleur()
    {
        return $this->couleur;
    }

    /**
     * Set json
     *
     * @param string $json
     * @return Groupe
     */
    public function setJson($json)
    {
        $this->json = $json;
    
        return $this;
    }

    /**
     * Get json
     *
     * @return string 
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set type
     *
     * @param \int $type
     * @return Groupe
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return \int 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add hierarchie
     *
     * @param \Sesile\UserBundle\Entity\UserGroupe $hierarchie
     * @return Groupe
     */
    public function addHierarchie(\Sesile\UserBundle\Entity\UserGroupe $hierarchie)
    {
        $this->hierarchie[] = $hierarchie;
    
        return $this;
    }

    /**
     * Remove hierarchie
     *
     * @param \Sesile\UserBundle\Entity\UserGroupe $hierarchie
     */
    public function removeHierarchie(\Sesile\UserBundle\Entity\UserGroupe $hierarchie)
    {
        $this->hierarchie->removeElement($hierarchie);
    }

    /**
     * Get hierarchie
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHierarchie()
    {
        return $this->hierarchie;
    }
}