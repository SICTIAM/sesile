<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 03/02/2015
 * Time: 15:00
 */

namespace Sesile\DocumentBundle\Classe;

use Sesile\DocumentBundle\Classe\Piece;

class Bordereau
{

    public $id;
    public $dateEm;
    public $nbPiece;
    public $mtBordHT;
    public $mtCumulAnnuel;
    public $exercice;
    public $type;
    public $listPieces = array();

    function __construct($id, $dateEm, $nbPiece, $mtBordHT, $mtCumulAnnuel, $exercice, $type, $listPieces, $typePES, $tabPJ2)
    {
        $this->id = $id;
        $this->dateEm = $dateEm;
        $this->nbPiece = $nbPiece;
        $this->mtBordHT = $mtBordHT;
        $this->mtCumulAnnuel = $mtCumulAnnuel;
        $this->exercice = $exercice;
        if ($typePES === 'Depense') {
            switch ($type) {
                case '01':
                    $this->type = 'Ordinaire';
                    break;
                case '02':
                    $this->type = 'Annulation/réduction';
                    break;
                case '03':
                    $this->type = 'Ordres de paiement';
                    break;
                case '04':
                    $this->type = 'Bordereau de régularisation';
                    break;
            }

        } else {
            switch ($type) {
                case '01':
                    $this->type = 'Ordinaire';
                    break;
                case '02':
                    $this->type = 'Annulation/réduction';
                    break;
                case '03':
                    $this->type = 'Ordre de recette';
                    break;
                case '04':
                    $this->type = 'Bordereau de titre émis suite à décision juridictionnelle';
                    break;
                case '05':
                    $this->type = 'Entête P503';
                    break;
                case '06':
                    $this->type = 'Bordereau Ordre de recette multi créanciers';
                    break;
            }
        }

        foreach ($listPieces as $piece) {

            if (isset($piece->BlocPiece->InfoPce)) {
                $idP = $piece->BlocPiece->InfoPce->IdPce->attributes()[0];
            } else {
                $idP = $piece->BlocPiece->IdPce->attributes()[0];
            }
            if (isset($piece->LigneDePiece->Tiers->InfoTiers->Civilite)) {
                $civilite = $piece->LigneDePiece->Tiers->InfoTiers->Civilite->attributes()[0];
            } else {
                $civilite = '';

            }
            $nom = $piece->LigneDePiece->Tiers->InfoTiers->Nom->attributes()[0];

            if (isset($piece->LigneDePiece->Tiers->InfoTiers->Prenom)) {
                $prenom = $piece->LigneDePiece->Tiers->InfoTiers->Prenom->attributes()[0];
            } else {
                $prenom = '';

            }
            $tabPJs = array();
            if ($typePES === 'Depense') {
                $objet = $piece->BlocPiece->InfoPce->Obj->attributes()[0];
                $tmpHT = doubleval($piece->LigneDePiece->BlocLignePiece->InfoLignePce->MtHT->attributes()[0]);
                $tmpTVA = doubleval($piece->LigneDePiece->BlocLignePiece->InfoLignePce->TVAIntraCom->attributes()[0]);
                if (isset($piece->BlocPiece->InfoPce->PJRef)) {
                    foreach ($piece->BlocPiece->InfoPce->PJRef as $pj) {
                        $tabPJs[] = $pj;
                    }
                }
            } else {
                $objet = $piece->BlocPiece->ObjPce->attributes()[0];
                $tmpHT = doubleval($piece->LigneDePiece->BlocLignePiece->InfoLignePiece->MtHT->attributes()[0]);
                $tmpTVA = doubleval($piece->LigneDePiece->BlocLignePiece->InfoLignePiece->TvaIntraCom->attributes()[0]);
                if (isset($piece->BlocPiece->PJRef)) {
                    foreach ($piece->BlocPiece->PJRef as $pj) {
                        $tabPJs[] = $pj;
                    }
                }
            }

            //formatage montant HT
            $mtHT = number_format($tmpHT, 2, ',', ' ');

            //formatage montant TVA
            $mtTVA = number_format($tmpTVA, 2, ',', ' ');


            //Calcul du TTC
            $mtTTCNum = tofloat($mtHT) + tofloat($mtTVA);

            $mtTTC = number_format($mtTTCNum, 2, ',', ' ');


            $this->listPieces[] = new Piece($idP, $civilite, $nom, $prenom, $objet, $mtHT, $mtTVA, $mtTTC, $tabPJs, $tabPJ2);
        }
    }
}

function tofloat($num)
{
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    }

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
    );
}