<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EtapeGroupe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\EtapeGroupeRepository")
 */
class EtapeGroupe
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="etapeGroupes", cascade={"persist"})
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", inversedBy="etapeGroupesUP", cascade={"persist"})
     * @Serializer\Groups({"listCircuitByCollectivite", "getByIdCircuit"})
     */
    private $userPacks;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\Groupe", inversedBy="etapeGroupes")
     * @ORM\JoinColumn(name="groupe", referencedColumnName="id")
     *
     */
    protected $groupe;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     * @Serializer\Groups({"getByIdCircuit"})
     *
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
     * Constructor
     */
    public function __construct()
    {

    }
    
    /**
     * Add users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     * @return EtapeGroupe
     */
    public function addUser(\Sesile\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Sesile\UserBundle\Entity\User $users
     */
    public function removeUser(\Sesile\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Get groupe
     *
     * @return \Sesile\ClasseurBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set groupe
     *
     * @param \Sesile\UserBundle\Entity\Groupe $groupe
     * @return EtapeGroupe
     */
    public function setGroupe(\Sesile\UserBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;
    
        return $this;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return EtapeGroupe
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
     * Add userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     * @return EtapeGroupe
     */
    public function addUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks[] = $userPacks;
    
        return $this;
    }

    /**
     * Remove userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     */
    public function removeUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks->removeElement($userPacks);
    }

    /**
     * Get userPacks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserPacks()
    {
        return $this->userPacks;
    }
}