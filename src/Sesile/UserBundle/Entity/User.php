<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;


/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getExpiresAt() {
        return $this->expiresAt;
    }

    public function getCredentialsExpireAt() {
        return $this->getCredentialsExpireAt();
    }

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="user")
     */
    protected $classeurs;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="validant")
     */
    protected $classeurs_a_valider;

    public function __construct() {
        $this->classeurs = new ArrayCollection();
        parent::__construct();
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
     * Add classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     * @return User
     */
    public function addClasseur(\Sesile\ClasseurBundle\Entity\Classeur $classeurs)
    {
        $this->classeurs[] = $classeurs;
    
        return $this;
    }

    /**
     * Remove classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     */
    public function removeClasseur(\Sesile\ClasseurBundle\Entity\Classeur $classeurs)
    {
        $this->classeurs->removeElement($classeurs);
    }

    /**
     * Get classeurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClasseurs()
    {
        return $this->classeurs;
    }

    /**
     * Add classeurs_a_valider
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursAValider
     * @return User
     */
    public function addClasseursAValider(\Sesile\ClasseurBundle\Entity\Classeur $classeursAValider)
    {
        $this->classeurs_a_valider[] = $classeursAValider;
    
        return $this;
    }

    /**
     * Remove classeurs_a_valider
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursAValider
     */
    public function removeClasseursAValider(\Sesile\ClasseurBundle\Entity\Classeur $classeursAValider)
    {
        $this->classeurs_a_valider->removeElement($classeursAValider);
    }

    /**
     * Get classeurs_a_valider
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClasseursAValider()
    {
        return $this->classeurs_a_valider;
    }
}