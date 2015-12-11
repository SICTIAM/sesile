<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sabre\VObject\Property\DateTime;

/**
 * DocumentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DocumentRepository extends EntityRepository
{
    /**
     * @param $doc
     * @param $classeurId
     * @param int $translateX
     * @param int $translateY
     * @param bool $first
     * @param $texteVisa
     * @param bool $color
     * @throws \SetaPDF_Core_Exception
     *
     * Affiche le fichier PDF avec le visa ou la signature directement dans le navigateur
     *
     */
    public function setaPDFTampon($doc, $classeurId, $translateX = 30, $translateY = -30, $first = true, $texteVisa, $color = false) {

        // Params
        $borderWidth = 1;
        $padding = 7;
        $fontSize = 12;
        $texteStamp = '';


        if ($first) {
//            $pagePosition = \SetaPDF_Stamper::PAGES_FIRST;
            $pagePosition = 'first';
        } else {
//            $pagePosition = \SetaPDF_Stamper::PAGES_LAST;
            $pagePosition = 'last';
        }

        $em = $this->getEntityManager();
        $actions = $em->getRepository('SesileClasseurBundle:Action')->findBy(array(
            'classeur' => $classeurId,
            'action' => array('Validation', 'Classeur finalisé')
        ));

        foreach ($actions as $action) {
            $texteStamp .= "\n" . $action->getUsername() . ' le ' . $action->getDate()->format('d/m/Y à h:i');
        }


        // Create a reader : le fichier sur lequel on va apposer le tampon
        $reader = 'uploads/docs/' . $doc;

        if($texteVisa) {

            // Color convert
            $colorRGB = $this->hex2rgb($color);
            $colorVisa = new \SetaPDF_Core_DataStructure_Color_Rgb($colorRGB[0] / 255, $colorRGB[1] / 255, $colorRGB[2] / 255);
            $colorVisaBorder = new \SetaPDF_Core_DataStructure_Color_Rgb(($colorRGB[0] + 80) / 255, ($colorRGB[1] + 80) / 255, ($colorRGB[2] + 80) / 255);

            // create a writer
            $writer = new \SetaPDF_Core_Writer_Http('VISA_' . $doc, true);

            // get a document instance
            $document = \SetaPDF_Core_Document::loadByFilename(
                $reader, $writer
            );

            // create a font object
            $font = \SetaPDF_Core_Font_Standard_Helvetica::create($document);

            // Stamp Visa
            $stamp_visa = new \SetaPDF_Stamper_Stamp_Text($font, $fontSize);
            $stamp_visa->setText($texteVisa);
            $stamp_visa->setAlign("center");
            $stamp_visa->setBorderWidth($borderWidth + 2);
            $stamp_visa->setBorderColor($colorVisa);
            $stamp_visa->setPadding($padding);
            $stamp_visa->setPaddingTop($padding + 3);
            $stamp_visa->setTextColor($colorVisa);


            // create a stamper instance
            $stamper = new \SetaPDF_Stamper($document);

            // create simple text stamp
            $stamp = new \SetaPDF_Stamper_Stamp_Text($font, $fontSize - 4);
            $stamp->setText($texteStamp);
            $stamp->setAlign("center");
            $stamp->setBorderWidth($borderWidth);
            $stamp->setBorderColor($colorVisaBorder);
            $stamp->setPadding($padding);
            $stamp->setPaddingTop($fontSize / 2 + $padding);
            $stamp->setTextColor($colorVisa);


            // right bottom and callback
            $stamper->addStamp($stamp, array(
                'showOnPage' => $pagePosition,
                'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
                'translateX' => $translateX + $padding * 2,
                'translateY' => $translateY
            ));

            $stamp_visa->setWidth($stamp->getWidth());
            $stamp_visa->setPaddingBottom($stamp->getHeight() - $fontSize + 3);

            $stamper->addStamp($stamp_visa, array(
//            'showOnPage' => '2-21',
                'showOnPage' => $pagePosition,
                'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
                'translateX' => $translateX + $padding,
                'translateY' => $translateY + $padding
            ));

        } else {
            // create a writer
            $writer = new \SetaPDF_Core_Writer_Http('VISA_' . $doc, true);
            // get a document instance
            $document = \SetaPDF_Core_Document::loadByFilename(
                $reader, $writer
            );

            // create a stamper instance
            $stamper = new \SetaPDF_Stamper($document);

            // get an image instance
            $image = \SetaPDF_Core_Image::getByPath('/home/sesile/web/uploads/avatars/af3a9b46ddeea18a0f4108fac421e46aa52a6c44.jpeg');
            // initiate the stamp
            $stamp = new \SetaPDF_Stamper_Stamp_Image($image);
            // set height (and width until no setWidth is set the ratio will retain)
            $stamp->setHeight(150);

            // add stamp to stamper on position left top for all pages with a specific translation
            $stamper->addStamp($stamp, array(
                'showOnPage' => $pagePosition,
                'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
                'translateX' => $translateX,
                'translateY' => $translateY
            ));
        }

        // stamp the document
        $stamper->stamp();

        // Show the the while page at a time
        $document->getCatalog()->setPageLayout(\SetaPDF_Core_Document_PageLayout::SINGLE_PAGE);

        // save and send it to the client
        $document->save()->finish();
    }

    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }


    public function tamponAlamano($historique, $color){

        //On récupère les informations nécessaires pour le tampon
        $date								= date("d/m/Y",$historique['date']);
        $numActe							= $historique['numActe'];

        // Taille du tampon
        $widthIMG = 226;
        $heightIMG = 50;

        // On crée une image
        $im = imagecreatetruecolor($widthIMG, $heightIMG);

        //On récupèré la couleur et le transparent
        $color 								= imagecolorallocate($im, $color[0], $color[1], $color[2]);
        $trans_colour                       = imagecolorallocatealpha($im, 0, 0, 0, 127);

        // On active la transparence et on rempli de transparent parce que pourquoi pas
        imagesavealpha($im, true);
        imagefill($im, 0, 0, $trans_colour);

        // On dessigne le petit cadre à l'interieur
        imageline($im, 6, 18, 218, 18, $color);
        imageline($im, 6, 18, 6, 41, $color);
        imageline($im, 218, 18, 218, 41, $color);
        imageline($im, 6, 41, 218, 41, $color);

        // On dessigne le gros cadre (bordure)
        imagesetthickness($im, 5);
        imageline($im, 0, 0, $widthIMG-1, 0, $color);
        imageline($im, 0, 0, 0, $heightIMG-1, $color);
        imageline($im, $widthIMG-1, 0, $widthIMG-1, $heightIMG-1, $color);
        imageline($im, 0, $heightIMG-1, $widthIMG-1, $heightIMG-1, $color);

        // On récupère le bon message comme type de tampon
        switch ($historique['image']) {
            case "AN.gif":
                $typeTampon = "ANOMALIE";
                break;
            case "AR.gif":
                $typeTampon = "AR PREFECTURE";
                break;
            case "ARN.gif":
                $typeTampon = "AR ANNULATION PREFECTURE";
                break;
            default:
                $typeTampon = "ANOMALIE";
        }

        // On calcule le centrage du texte
        $fw = imagefontwidth(2); // width of a character
        $l = strlen($typeTampon); // number of characters
        $tw = $l * $fw;              // text width
        $iw = imagesx($im);          // image width

        $xpos = ($iw - $tw)/2;
        $ypos = 4;

        // On écrit le type de tampon
        imagestring($im, 2, $xpos, $ypos, $typeTampon, $color);


        //On écrit dedans les informations souhaitées
        imagestring($im, 1, 10, 21, $numActe, $color);
        imagestring($im, 1, 10, 31, utf8_decode("Reçu le ".$date), $color);

        return $im;
    }

}
