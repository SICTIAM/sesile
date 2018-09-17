<?php

namespace Sesile\Utils;
use JMS\Serializer\Annotation\Groups;


class ListPagination
{
    /**
     * @var array
     *
     * @Groups({"listClasseur"})
     */
    private $list;

    /**
     * @var int
     *
     * @Groups({"listClasseur"})
     */
    private $nbElementInList;

    /**
     * @var int
     *
     * @Groups({"listClasseur"})
     */
    private $nbElementTotalOfEntity;

    /**
     * ListPagination constructor.
     * @param $list
     * @param $nbElementInList
     * @param $nbElementTotalOfEntity
     */
    public function __construct($list, $nbElementInList, $nbElementTotalOfEntity)
    {
        $this->list = $list;
        $this->nbElementInList = $nbElementInList;
        $this->nbElementTotalOfEntity = $nbElementTotalOfEntity;
    }
}