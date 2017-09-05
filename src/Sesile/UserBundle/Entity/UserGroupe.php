<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHierarchie
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserGroupeRepository")
 */
class UserGroupe
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
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="hierarchie")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;


    /**
     * @ORM\Column(name="parent", type="integer")
     */
    private $parent;


    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\Groupe", inversedBy="hierarchie")
     * @ORM\JoinColumn(name="groupe", referencedColumnName="id", onDelete="CASCADE")
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
     * Set parent
     *
     * @param integer $parent
     * @return UserGroupe
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return integer 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set user
     *
     * @param \Sesile\UserBundle\Entity\User $user
     * @return UserGroupe
     */
    public function setUser(\Sesile\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Sesile\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set groupe
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupe
     * @return UserGroupe
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
}