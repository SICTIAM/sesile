<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;


/**
 * Action
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ActionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Action
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Expose
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", inversedBy="actions")
     * @ORM\JoinColumn(name="classeur_id", referencedColumnName="id")
     *
     * @Expose
     */
    private $classeur;



    /**
     * @var string
     *
     * @ORM\Column(name="observation", type="text", nullable=true)
     * @Expose
     *
     */
    private $observation;


    /**
     * @var integer
     *
     * @ORM\Column(name="username", type="string", length=255)
     *
     * @Expose
     */
    private $username;



    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     *
     * @Expose
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="text")
     * @Expose
     *
     */
    private $action;


    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     * @Expose
     *
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
     * @ORM\PrePersist
     */
    public function setUsernameValue()
    {
        $this->username = $this->user->getNom() . " " . $this->user->getPrenom();
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Action
     */
    private function setUsername($username)
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

}