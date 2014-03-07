<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHierarchie
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserHierarchieRepository")
 */
class UserHierarchie
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
     * @ORM\Column(name="UserId", type="string", length=255)
     */
    private $user;


    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\UserHierarchie", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\UserHierarchie", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\Groupe", inversedBy="hierarchie")
     * @ORM\JoinColumn(name="groupe", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    private $groupe;


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
     * Set userId
     *
     * @param string $userId
     * @return UserHierarchie
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }



    /**
     * Set groupe
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupe
     * @return UserHierarchie
     */
    public function setGroupe(\Sesile\UserBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;
    
        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Sesile\UserBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add children
     *
     * @param \Sesile\UserBundle\Entity\UserHierarchy $children
     * @return UserHierarchie
     */
    public function addChildren(\Sesile\UserBundle\Entity\UserHierarchy $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Sesile\UserBundle\Entity\UserHierarchy $children
     */
    public function removeChildren(\Sesile\UserBundle\Entity\UserHierarchy $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Sesile\UserBundle\Entity\UserHierarchy $parent
     * @return UserHierarchie
     */
    public function setParent(\Sesile\UserBundle\Entity\UserHierarchy $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Sesile\UserBundle\Entity\UserHierarchy 
     */
    public function getParent()
    {
        return $this->parent;
    }
}