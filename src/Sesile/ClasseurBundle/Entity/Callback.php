<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Sesile\UserBundle\Entity\User;


/**
 * Callback
 *
 * @property  Callback
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\CallbackRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Callback
{
    /**
     * @var integer
     *
     * @ORM\Column(name="classeur_id", type="integer")
     *
     */
    private $classeur_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     *
     */
    private $id;
    /**
     * @var string
     * @ORM\Id
     *
     * @ORM\Column(name="event", type="string")
     *
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string")
     *
     */
    private $url;


    public function setId($classeurId) {
        $this->classeur_id = $classeurId;
    }
    public function setEvent($event) {
        $this->event = $event;
    }
    public function setUrl($url) {
        $this->url = $url;
    }
}