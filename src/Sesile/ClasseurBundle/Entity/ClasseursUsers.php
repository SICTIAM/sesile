<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClasseursUsers
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseursUsersRepository")
 */
class ClasseursUsers
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur")
     */
    private $classeur;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return ClasseursUsers
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set classeur
     *
     * @param integer $classeur
     * @return ClasseursUsers
     */
    public function setClasseur($classeur)
    {
        $this->classeur = $classeur;

        return $this;
    }

    /**
     * Get classeur
     *
     * @return integer
     */
    public function getClasseur()
    {
        return $this->classeur;
    }

    /**
     * Set user
     *
     * @param integer $user
     * @return ClasseursUsers
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }
}