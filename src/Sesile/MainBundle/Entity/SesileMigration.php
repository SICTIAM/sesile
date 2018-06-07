<?php

namespace Sesile\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SesileMigration
 *
 * @ORM\Table("sesile_migration")
 * @ORM\Entity(repositoryClass="Sesile\MainBundle\Repository\SesileMigrationRepository")
 */
class SesileMigration
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
     * @ORM\Column(name="collectivity", type="string", length=255)
     */
    private $collectivity;

    /**
     * @var string legacy collectivity id
     *
     * @ORM\Column(name="old_id", type="string")
     */
    private $oldId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    /**
     * @return string
     */
    public function getCollectivity()
    {
        return $this->collectivity;
    }

    /**
     * @param string $collectivity
     *
     * @return SesileMigration
     */
    public function setCollectivity($collectivity)
    {
        $this->collectivity = $collectivity;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldId()
    {
        return $this->oldId;
    }

    /**
     * @param string $oldId
     *
     * @return SesileMigration
     */
    public function setOldId($oldId)
    {
        $this->oldId = $oldId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return SesileMigration
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }
}