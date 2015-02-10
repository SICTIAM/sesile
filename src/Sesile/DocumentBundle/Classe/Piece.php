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
    public $mtHT;
    public $mtTVA;
    public $mtTTC;
    public $listePJs = array();

    //public $listPJ=array();

    function __construct($id, $civilite, $nom, $prenom, $objet, $mtHT, $mtTVA, $mtTTC, $tabPJ, $tabPJ2)
    {
        $this->id = $id;
        $this->civilite = $civilite;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->objet = $objet;
        $this->mtHT = $mtHT;
        $this->mtTVA = $mtTVA;
        $this->mtTTC = $mtTTC;
        foreach ($tabPJ as $pj) {
            $idPJ = $pj->IdUnique->attributes()[0];
            $nomPJ = $pj->NomPJ->attributes()[0];
            foreach ($tabPJ2 as $fic) {
                $fromlpj = $fic->IdUnique->attributes()[0];
                $test = $fromlpj[0];
                if (strcmp($fromlpj, $idPJ) == 0) {

                    $this->listePJs[] = new PJ($idPJ, $nomPJ, $fic->Contenu->Fichier);
                }
            }


        }
    }
} 