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

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function getCredentialsExpireAt()
    {
        return $this->getCredentialsExpireAt();
    }

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="user")
     */
    protected $classeurs_deposes;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="validant")
     */
    protected $classeurs_a_valider;


    public function __construct()
    {
        $this->$classeurs_a_valider = new ArrayCollection();
        $this->$classeurs_deposes = new ArrayCollection();
        $this->$classeurs = new ArrayCollection();
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
     * Add classeurs_deposes
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursDeposes
     * @return User
     */
    public function addClasseursDepose(\Sesile\ClasseurBundle\Entity\Classeur $classeursDeposes)
    {
        $this->classeurs_deposes[] = $classeursDeposes;

        return $this;
    }

    /**
     * Remove classeurs_deposes
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursDeposes
     */
    public function removeClasseursDepose(\Sesile\ClasseurBundle\Entity\Classeur $classeursDeposes)
    {
        $this->classeurs_deposes->removeElement($classeursDeposes);
    }

    /**
     * Get classeurs_deposes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClasseursDeposes()
    {
        return $this->classeurs_deposes;
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