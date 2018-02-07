<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 03/02/2015
 * Time: 15:00
 */

namespace Sesile\DocumentBundle\Classe;

use Sesile\DocumentBundle\Classe\PJ;

class Piece
{

    public $id;
    public $civilite;
    public $nom;
    public $prenom;
    public $objet;
    public $imputations;
    public $mtHT;
    public $mtTVA;
    public $mtTTC;
    public $listePJs = array();

    function __construct($id, $civilite, $nom, $prenom, $objet,$imputation, $mtHT, $mtTVA, $mtTTC, $tabPJ, $tabPJ2)
    {

        $this->id = $id;
        $this->civilite = $civilite;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->objet = $objet;
        $this->mtHT = $mtHT;
        $this->mtTVA = $mtTVA;
        $this->mtTTC = $mtTTC;
        $this->imputations = $imputation;
        foreach ($tabPJ as $pj) {
            $idPJ = (string)$pj->IdUnique->attributes()[0];
            $nomPJ = (string)$pj->NomPJ->attributes()[0];
            foreach ($tabPJ2 as $fic) {
                $fromlpj = (string)$fic->IdUnique->attributes()[0];
                if (strcmp($fromlpj, $idPJ) == 0) {
                    $this->listePJs[] = new PJ($idPJ, $nomPJ);
                }
            }
        }
    }
} 