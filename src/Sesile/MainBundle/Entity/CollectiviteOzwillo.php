<?php

namespace Sesile\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CollectiviteOzwillo
 *
 * @ORM\Table(name="collectivite_ozwillo")
 * @ORM\Entity(repositoryClass="Sesile\MainBundle\Repository\CollectiviteOzwilloRepository")
 */
class CollectiviteOzwillo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="ozwillo")
     * @ORM\JoinColumn(name="collectivite_id", referencedColumnName="id")
     */
    private $collectivite;

    /**
     * @var string
     *
     * @ORM\Column(name="instanceId", type="string", length=100, unique=true)
     */
    private $instanceId;

    /**
     * @var string
     *
     * @ORM\Column(name="clientId", type="string", length=100)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="clientSecret", type="string", length=100)
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="instanceRegistrationUri", type="string", length=255)
     */
    private $instanceRegistrationUri;

    /**
     * @var string
     *
     * @ORM\Column(name="destructionSecret", type="string", length=100, nullable=true)
     */
    private $destructionSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="statusChangedSecret", type="string", length=100, nullable=true)
     */
    private $statusChangedSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="creatorId", type="string", length=100, nullable=true)
     */
    private $creatorId;

    /**
     * @var string
     *
     * @ORM\Column(name="creatorName", type="string", length=150, nullable=true)
     */
    private $creatorName;

    /**
     * @var string
     *
     * @ORM\Column(name="dcId", type="string", length=100, nullable=true)
     */
    private $dcId;

    /**
     * @var bool
     *
     * @ORM\Column(name="notifiedToKernel", type="boolean", nullable=true)
     */
    private $notifiedToKernel;

    /**
     * @var string
     *
     * @ORM\Column(name="serviceId", type="string", length=100, nullable=true)
     */
    private $serviceId;


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
     * Set instanceId
     *
     * @param string $instanceId
     *
     * @return CollectiviteOzwillo
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * Get instanceId
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set clientId
     *
     * @param string $clientId
     *
     * @return CollectiviteOzwillo
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientSecret
     *
     * @param string $clientSecret
     *
     * @return CollectiviteOzwillo
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get clientSecret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set instanceRegistrationUri
     *
     * @param string $instanceRegistrationUri
     *
     * @return CollectiviteOzwillo
     */
    public function setInstanceRegistrationUri($instanceRegistrationUri)
    {
        $this->instanceRegistrationUri = $instanceRegistrationUri;

        return $this;
    }

    /**
     * Get instanceRegistrationUri
     *
     * @return string
     */
    public function getInstanceRegistrationUri()
    {
        return $this->instanceRegistrationUri;
    }

    /**
     * Set destructionSecret
     *
     * @param string $destructionSecret
     *
     * @return CollectiviteOzwillo
     */
    public function setDestructionSecret($destructionSecret)
    {
        $this->destructionSecret = $destructionSecret;

        return $this;
    }

    /**
     * Get destructionSecret
     *
     * @return string
     */
    public function getDestructionSecret()
    {
        return $this->destructionSecret;
    }

    /**
     * Set statusChangedSecret
     *
     * @param string $statusChangedSecret
     *
     * @return CollectiviteOzwillo
     */
    public function setStatusChangedSecret($statusChangedSecret)
    {
        $this->statusChangedSecret = $statusChangedSecret;

        return $this;
    }

    /**
     * Get statusChangedSecret
     *
     * @return string
     */
    public function getStatusChangedSecret()
    {
        return $this->statusChangedSecret;
    }

    /**
     * Set creatorId
     *
     * @param string $creatorId
     *
     * @return CollectiviteOzwillo
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    /**
     * Get creatorId
     *
     * @return string
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * Set creatorName
     *
     * @param string $creatorName
     *
     * @return CollectiviteOzwillo
     */
    public function setCreatorName($creatorName)
    {
        $this->creatorName = $creatorName;

        return $this;
    }

    /**
     * Get creatorName
     *
     * @return string
     */
    public function getCreatorName()
    {
        return $this->creatorName;
    }

    /**
     * Set dcId
     *
     * @param string $dcId
     *
     * @return CollectiviteOzwillo
     */
    public function setDcId($dcId)
    {
        $this->dcId = $dcId;

        return $this;
    }

    /**
     * Get dcId
     *
     * @return string
     */
    public function getDcId()
    {
        return $this->dcId;
    }

    /**
     * Set notifiedToKernel
     *
     * @param boolean $notifiedToKernel
     *
     * @return CollectiviteOzwillo
     */
    public function setNotifiedToKernel($notifiedToKernel)
    {
        $this->notifiedToKernel = $notifiedToKernel;

        return $this;
    }

    /**
     * Get notifiedToKernel
     *
     * @return bool
     */
    public function getNotifiedToKernel()
    {
        return $this->notifiedToKernel;
    }

    /**
     * Set serviceId
     *
     * @param string $serviceId
     *
     * @return CollectiviteOzwillo
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get serviceId
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set collectivite
     *
     * @param \Sesile\MainBundle\Entity\Collectivite $collectivite
     *
     * @return CollectiviteOzwillo
     */
    public function setCollectivite(\Sesile\MainBundle\Entity\Collectivite $collectivite = null)
    {
        $this->collectivite = $collectivite;

        return $this;
    }

    /**
     * Get collectivite
     *
     * @return \Sesile\MainBundle\Entity\Collectivite
     */
    public function getCollectivite()
    {
        return $this->collectivite;
    }
}
