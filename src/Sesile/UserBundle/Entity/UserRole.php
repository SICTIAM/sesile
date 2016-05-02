<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRole
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserRoleRepository")
 */
class UserRole
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
     * @ORM\Column(name="userRoles", type="string", length=150)
     */
    private $userRoles;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", fetch="EAGER", inversedBy="userRole")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

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
     * Set userRoles
     *
     * @param string $userRoles
     * @return UserRole
     */
    public function setUserRoles($userRoles)
    {
        $this->userRoles = $userRoles;
    
        return $this;
    }

    /**
     * Get userRoles
     *
     * @return string 
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * Set user
     *
     * @param \Sesile\UserBundle\Entity\User $user
     * @return UserRole
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
}