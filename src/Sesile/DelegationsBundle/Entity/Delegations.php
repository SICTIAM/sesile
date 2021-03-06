<?php

namespace Sesile\DelegationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Delegations
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\DelegationsBundle\Entity\DelegationsRepository")
 */
class Delegations
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
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="delegationsDonnees")
     * @ORM\JoinColumn(name="delegant", referencedColumnName="id", onDelete="CASCADE")
     */
    private $delegant;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="delegationsRecues")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var \Date
     *
     * @ORM\Column(name="debut", type="date")
     */
    private $debut;

    /**
     * @var \Date
     *
     * @ORM\Column(name="fin", type="date")
     */
    private $fin;


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
     * Set user
     *
     * @param integer $user
     * @return Delegations
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

    /**
     * Set debut
     *
     * @param \Date $debut
     * @return Delegations
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get debut
     *
     * @return \Date
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param \Date $fin
     * @return Delegations
     */
    public function setFin($fin)
    {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get fin
     *
     * @return \Date
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set delegant
     *
     * @param \Sesile\UserBundle\Entity\User $delegant
     * @return Delegations
     */
    public function setDelegant(\Sesile\UserBundle\Entity\User $delegant = null)
    {
        $this->delegant = $delegant;

        return $this;
    }

    /**
     * Get delegant
     *
     * @return \Sesile\UserBundle\Entity\User
     */
    public function getDelegant()
    {
        return $this->delegant;
    }
}