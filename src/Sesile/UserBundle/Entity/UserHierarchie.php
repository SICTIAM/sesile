<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHierarchie
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserHierarchieRepository")
 */
class UserHierarchie
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
     * @ORM\Column(name="UserId", type="string", length=255)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="ParentId", type="string", length=255)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="GroupeId", type="string", length=255)
     */
    private $groupeId;


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
     * Set userId
     *
     * @param string $userId
     * @return UserHierarchie
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set parentId
     *
     * @param string $parentId
     * @return UserHierarchie
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return string
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set groupeId
     *
     * @param string $groupeId
     * @return UserHierarchie
     */
    public function setGroupeId($groupeId)
    {
        $this->groupeId = $groupeId;

        return $this;
    }

    /**
     * Get groupeId
     *
     * @return string
     */
    public function getGroupeId()
    {
        return $this->groupeId;
    }
}
