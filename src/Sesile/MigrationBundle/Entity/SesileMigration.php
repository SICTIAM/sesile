<?php

namespace Sesile\MigrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SesileMigration
 *
 * @ORM\Table("sesile_migration")
 * @ORM\Entity(repositoryClass="Sesile\MigrationBundle\Entity\SesileMigrationRepository")
 */
class SesileMigration
{
    const STATUS_EN_COURS = 'EN_COURS';
    const STATUS_FINALISE = 'FINALISE';

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
     * @ORM\Column(name="collectivity_id", type="string", length=255)
     */
    private $collectivityId;

    /**
     * @var string
     *
     * @ORM\Column(name="collectivity_name", type="string", length=255, nullable=true)
     */
    private $collectivityName;

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=9)
     */
    private $siren;
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10)
     */
    private $status;
    /**
     * @var boolean
     *
     * @ORM\Column(name="users_exported", type="boolean")
     */
    private $usersExported;

    /**
     * @var string legacy collectivity id
     *
     * @ORM\Column(name="old_id", type="string", nullable=true)
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

    /**
     * SesileMigration constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->usersExported = false;
        $this->status = self::STATUS_EN_COURS;
    }

    /**
     * @return string
     */
    public function getCollectivityId()
    {
        return $this->collectivityId;
    }

    /**
     * @param string $collectivityId
     *
     * @return SesileMigration
     */
    public function setCollectivityId($collectivityId)
    {
        $this->collectivityId = $collectivityId;

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

    /**
     * @return string
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * @param string $siren
     *
     * @return SesileMigration
     */
    public function setSiren($siren)
    {
        $this->siren = $siren;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * returns false if status is not correct
     *
     * @return SesileMigration
     * @throws \Exception
     */
    public function setStatus($status)
    {
        if (!in_array($status, [self::STATUS_EN_COURS, self::STATUS_FINALISE])) {
            throw new \Exception(
                sprintf(
                    'SesileMigration Status %s is not valid. Accepted values: %s, %s',
                    $status,
                    self::STATUS_EN_COURS,
                    self::STATUS_FINALISE
                )
            );
        }
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsersExported()
    {
        return $this->usersExported;
    }

    /**
     * @param bool $usersExported
     *
     * @return SesileMigration
     */
    public function setUsersExported($usersExported)
    {
        $this->usersExported = $usersExported;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollectivityName()
    {
        return $this->collectivityName;
    }

    /**
     * @param string $collectivityName
     *
     * @return SesileMigration
     */
    public function setCollectivityName($collectivityName)
    {
        $this->collectivityName = $collectivityName;

        return $this;
    }
}