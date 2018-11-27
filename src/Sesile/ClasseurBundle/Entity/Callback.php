<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="classeurId", type="integer")
     *
     */
    private $classeurId;

    /**
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     *
     * @ORM\Column(name="url", type="string")
     *
     */
    private $url;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getClasseurId(): int
    {
        return $this->classeurId;
    }

    /**
     * @param int $classeurId
     */
    public function setClasseurId(int $classeurId)
    {
        $this->classeurId = $classeurId;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}