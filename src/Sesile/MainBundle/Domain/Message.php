<?php


namespace Sesile\MainBundle\Domain;

/**
 * Class Message
 * Intended to be the base of return for services
 *
 * @package Sesile\MainBundle\Domain
 */
final class Message
{
    /**
     * @var bool
     */
    private $result;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var mixed
     */
    private $data;

    /**
     * Message constructor.
     * @param bool  $result
     * @param mixed $data
     * @param array $errors
     */
    public function __construct($result, $data, array $errors = [])
    {
        $this->result = $result;
        $this->data = $data;
        $this->errors = $errors;
    }

    /**
     * @return boolean
     */
    public final function isSuccess()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public final function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public final function getData()
    {
        return $this->data;
    }

}