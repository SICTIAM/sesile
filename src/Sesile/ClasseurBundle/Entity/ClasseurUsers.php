<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClasseurUsers
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseurUsersRepository")
 */
class ClasseurUsers
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
     * @var integer
     *
     * @ORM\Column(name="classeur_id", type="integer")
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="users")
     * @ORM\JoinColumn(name="classeur_id", referencedColumnName="id")
     */
    private $classeurId;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

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
     * Set classeurId
     *
     * @param integer $classeurId
     * @return ClasseurUsers
     */
    public function setClasseurId($classeurId)
    {
        $this->classeurId = $classeurId;
    
        return $this;
    }

    /**
     * Get classeurId
     *
     * @return integer 
     */
    public function getClasseurId()
    {
        return $this->classeurId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return ClasseurUsers
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return ClasseurUsers
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
}