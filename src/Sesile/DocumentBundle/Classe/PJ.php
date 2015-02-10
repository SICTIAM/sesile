<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 03/02/2015
 * Time: 15:01
 */

namespace Sesile\DocumentBundle\Classe;

class PJ
{

    public $id;
    public $nom;
    public $content;

    function __construct($id, $nom, $content)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->content = $content;
    }
}