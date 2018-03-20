<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sesile\UserBundle\Entity\User as User;
use JMS\Serializer\Annotation as Serializer;

/**
 * Note
 *
 * @ORM\Table(name="note")
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Repository\NoteRepository")
 */
class Note
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"noteMaj", "listNote", "detailsNote", "getLastNote"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Serializer\Groups({"noteMaj", "getLastNote", "listNote", "detailsNote"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     * @Serializer\Groups({"noteMaj", "getLastNote", "listNote", "detailsNote"})
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     * @Serializer\Groups({"noteMaj", "getLastNote", "detailsNote"})
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     * @Serializer\Groups({"noteMaj", "listNote"})
     */
    private $created;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", mappedBy="notes", cascade={"persist"})
     */
    private $users;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime('now');
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Note
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Note
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return Note
     */
    public function setSubtitle(string $subtitle): Note
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * Add user
     *
     * @param \Sesile\UserBundle\Entity\User $user
     *
     * @return Note
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Sesile\UserBundle\Entity\User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
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

}
