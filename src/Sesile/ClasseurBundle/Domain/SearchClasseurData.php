<?php


namespace Sesile\ClasseurBundle\Domain;

/**
 * Class SearchClasseurData
 * used for the classeur search methods
 *
 * @package Sesile\ClasseurBundle\Domain
 */
class SearchClasseurData
{
    /**
     * @var string $name
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return SearchClasseurData
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}