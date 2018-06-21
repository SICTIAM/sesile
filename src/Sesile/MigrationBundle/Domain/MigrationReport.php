<?php


namespace Sesile\MigrationBundle\Domain;

use Sesile\MainBundle\Entity\CollectiviteOzwillo;

/**
 * Class MigrationReport
 * @package Sesile\MigrationBundle\Domain
 */
class MigrationReport
{
    /**
     * @var array
     */
    private $users;
    /**
     * @var string
     */
    private $organizationId;
    /**
     * @var string
     */
    private $instanceId;
    /**
     * @var string
     */
    private $creatorId;
    /**
     * @var string
     */
    private $serviceId;

    /**
     * MigrationReport constructor.
     * @param array $users
     * @param CollectiviteOzwillo $collectiviteOzwillo
     * @param string $creatorId
     */
    public function __construct(array $users = [], CollectiviteOzwillo $collectiviteOzwillo, $creatorId)
    {

        $this->users = $users;
        $this->organizationId = $collectiviteOzwillo->getOrganizationId();
        $this->instanceId = $collectiviteOzwillo->getInstanceId();
        $this->creatorId = $creatorId;
        $this->serviceId = $collectiviteOzwillo->getServiceId();
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     *
     * @return MigrationReport
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param string $organizationId
     *
     * @return MigrationReport
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param string $instanceId
     *
     * @return MigrationReport
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param string $creatorId
     *
     * @return MigrationReport
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param string $serviceId
     *
     * @return MigrationReport
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @return int
     */
    public function countUsers()
    {
        return count($this->users);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'countUsers' => $this->countUsers(),
            'organizationId' => $this->getOrganizationId(),
            'instanceId' => $this->getInstanceId(),
            'creatorId' => $this->getCreatorId(),
            'serviceId' => $this->getServiceId(),
        ];
    }

}