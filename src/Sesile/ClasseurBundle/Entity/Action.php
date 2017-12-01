<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Sesile\UserBundle\Entity\User;


/**
 * Action
 *
 * @property  user
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ActionRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class Action
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups("classeurById")
     *  
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="actions")
     * @ORM\JoinColumn(name="classeur_id", referencedColumnName="id")
     *
     *  
     */
    private $classeur;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_action", referencedColumnName="id", onDelete="CASCADE")
     * @Groups("classeurById")
     *
     */
    private $user_action;



    /**
     * @var string
     *
     * @ORM\Column(name="observation", type="text", nullable=true)
     * @Groups("classeurById")
     *
     */
    private $observation;


    /**
     * @var integer
     *
     * @ORM\Column(name="username", type="string", length=255)
     *
     * @Groups("classeurById")
     */
    private $username;



    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     *
     * @Groups("classeurById")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="text")
     *  
     * @Groups("classeurById")
     */
    private $action;


    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     *  
     * @Groups("classeurById")
     */
    private $commentaire;


    /**
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * @param string $commentaire
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;
    }


    /**
     * @return string
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * @param string $observation
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;
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
     * Set classeur
     *
     * @param integer $classeur
     * @return Action
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
     * @return Action
     */
    public function setUser($user)
    {
        $this->user = $user;
        $this->username = $user->getPrenom() . ' ' . $user->getNom();
        $this->user_action = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->username;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Action
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Action
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDateValue()
    {
        $this->date = new \DateTime();
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Action
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Set user_action
     *
     * @param User $userAction
     * @return Action
     */
    public function setUserAction(User $userAction = null)
    {
        $this->user_action = $userAction;
    
        return $this;
    }

    /**
     * Get user_action
     *
     * @return User
     */
    public function getUserAction()
    {
        return $this->user_action;
    }
}
