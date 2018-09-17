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
    public $vouchers = array();
    public $signatory;
    public $dateSign;
    public $typePesName;
    public $isPj = false;

    function __construct($xml)
    {
        $this->budget = (string)$xml->EnTetePES->LibelleColBud->attributes()[0];
        $this->getSignature($xml);
        $this->getBordereaux($xml);
    }

    private function getSignature($xml) {
        if (isset($xml->PES_DepenseAller) && count($xml->PES_DepenseAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_DepenseAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $this->signatory = utf8_decode($subject['CN']);

            //on récupère la date de signature (il y a surement plus simple)
            $this->dateSign = new \DateTime($xml->PES_DepenseAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime);


        } elseif(isset($xml->PES_RecetteAller) && count($xml->PES_RecetteAller->Bordereau->Signature)) {
            //si on a une signature  on récupère le certificat
            $sign = $xml->PES_RecetteAller->Bordereau->Signature->KeyInfo->X509Data->X509Certificate;
            $x509 = '-----BEGIN CERTIFICATE-----' . chr(10) . $sign . chr(10) . '-----END CERTIFICATE-----';
            //on récupère un tableau contenant les infos du certificat
            $tab = openssl_x509_parse($x509);
            $subject = $tab['subject'];
            $this->signatory = utf8_decode($subject['CN']);

            //on récupère la date de signature (il y a surement plus simple)
            $this->dateSign = new \DateTime($xml->PES_RecetteAller->Bordereau->Signature->Object->QualifyingProperties->SignedProperties->SignedSignatureProperties->SigningTime);

        }
        else{
            $this->signatory = '';
            $this->dateSign = '';
        }
    }

    protected function getBordereaux($xml) {
        $PJs = array();
        $bordereaux = array();
        if (isset($xml->PES_PJ)) {
            foreach ($xml->PES_PJ->PJ as $pj) {
                $PJs[] = $pj;
            }
        }

        if (isset($xml->PES_DepenseAller)) {
            $this->typePesName = 'dépense';
            foreach ($xml->PES_DepenseAller->Bordereau as $bordereau) {
                $bordereau->type = 'Depense';
                $bordereaux[] = $this->getBordereau($bordereau, $PJs);
            }
        }
        if (isset($xml->PES_RecetteAller)) {
            $this->typePesName = 'recette';
            foreach ($xml->PES_RecetteAller->Bordereau as $bordereau) {
                $bordereau->type = 'Recette';
                $bordereaux[] = $this->getBordereau($bordereau, $PJs);
            }
        }
        if(!isset($xml->PES_RecetteAller) && !isset($xml->PES_DepenseAller)) {
            //return array('isPJ' => true);
            $this->isPj = true;
        }

    }

    protected function getBordereau($bordereau, $PJs) {
        $id = (int)$bordereau->BlocBordereau->IdBord->attributes()[0];
        $dateEm = new \DateTime($bordereau->BlocBordereau->DteBordEm->attributes()[0]);
        $nbPiece = (int)$bordereau->BlocBordereau->NbrPce->attributes()[0];

        if ((string)$bordereau->type === 'Depense') {
            $tmpHT = doubleval($bordereau->BlocBordereau->MtBordHT->attributes()[0]);
        } else {
            //non non c'est pas la même chsoe qu'en haut regarde bien
            $tmpHT = doubleval($bordereau->BlocBordereau->MtBordHt->attributes()[0]);
        }

        $mtBordHT = number_format($tmpHT, 2, ',', ' ');
        $tmpCumulAnnuel = doubleval($bordereau->BlocBordereau->MtCumulAnnuel->attributes()[0]);
        $mtCumulAnnuel = number_format($tmpCumulAnnuel, 2, ',', ' ');
        $exercice = (int)$bordereau->BlocBordereau->Exer->attributes()[0];
        $typeBord = (string)$bordereau->BlocBordereau->TypBord->attributes()[0];

        $tabPiece = array();
        foreach ($bordereau->Piece as $piece) {
            $tabPiece[] = $piece;
        }
        $this->vouchers[] = new Bordereau($id, $dateEm, $nbPiece, $mtBordHT, $mtCumulAnnuel, $exercice, $typeBord, $tabPiece, (string)$bordereau->type, $PJs);
    }
} 