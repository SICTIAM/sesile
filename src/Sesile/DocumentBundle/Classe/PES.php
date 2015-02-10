<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 03/02/2015
 * Time: 14:59
 */

namespace Sesile\DocumentBundle\Classe;

use Sesile\DocumentBundle\Classe\Bordereau;

class PES
{

    public $budget;
    public $listBord = array();

    function __construct($budget, $tabBord, $typePES, $listPJ)
    {
        $this->budget = $budget;

        foreach ($tabBord as $bordereau) {

            $id = $bordereau->BlocBordereau->IdBord->attributes()[0];

            //Formatage Date Emission
            $dateEmWFormat = $bordereau->BlocBordereau->DteBordEm->attributes()[0];
            list($y, $m, $d) = explode('-', $dateEmWFormat);

            $dateEm = $d . '/' . $m . '/' . $y;

            $nbPiece = $bordereau->BlocBordereau->NbrPce->attributes()[0];
            if ($typePES == 'Depense') {
                $tmpHT = doubleval($bordereau->BlocBordereau->MtBordHT->attributes()[0]);
            } else {
                //non non c'est pas la même chsoe qu'en haut regarde bien
                $tmpHT = doubleval($bordereau->BlocBordereau->MtBordHt->attributes()[0]);
            }

            $mtBordHT = number_format($tmpHT, 2, ',', ' ');

            $tmpCumulAnnuel = doubleval($bordereau->BlocBordereau->MtCumulAnnuel->attributes()[0]);

            $mtCumulAnnuel = number_format($tmpCumulAnnuel, 2, ',', ' ');


            $exercice = $bordereau->BlocBordereau->Exer->attributes()[0];

            $type = $bordereau->BlocBordereau->TypBord->attributes()[0];


            $tabPiece = array();
            foreach ($bordereau->Piece as $piece) {
                $tabPiece[] = $piece;

            }

            $this->listBord[] = new Bordereau($id, $dateEm, $nbPiece, $mtBordHT, $mtCumulAnnuel, $exercice, $type, $tabPiece, $typePES, $listPJ);
            //exit;
            //error_log(print_r('TEST'));
        }
    }
} 