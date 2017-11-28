<?php

namespace Sesile\DocumentBundle\Entity;

use Doctrine\ORM\EntityRepository;
//use Sabre\VObject\Property\DateTime;

/**
 * DocumentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DocumentRepository extends EntityRepository
{

    public function uploadDocument($file, $classeur, $dirPath) {

        if ($file) {

            $em = $this->getEntityManager();
            $fileName = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
            $document = new Document();

            $document->setName($file->getClientOriginalName());
            $document->setType($file->getClientMimeType());
            $document->setRepourl($fileName);
            $document->setSigned(false);
            $document->setClasseur($classeur);

            $file->move(
                $dirPath,
                $fileName
            );

            $em->persist($document);
            $em->flush();

            return $document;
        }
    }

    public function removeDocument($file) {

        if (is_file($file)) {
            unlink($file);

            return true;
        } else {
            return false;
        }

    }

    /**
     * @param $doc
     * @return \SetaPDF_Core_Document
     *
     * Initialisation de setaPDF
     */
    protected function init_setaPDF($doc, $path) {

        // Create a reader : le fichier sur lequel on va apposer le tampon
        $reader = $path . $doc;

        // create a writer - 2nd parameter : true -> display inline, false -> download
        $writer = new \SetaPDF_Core_Writer_Http('VISA_' . $doc, true);

        // get a document instance
        $document = \SetaPDF_Core_Document::loadByFilename(
            $reader, $writer
        );

        return $document;
    }
    /**
     * @param $doc
     * @return \SetaPDF_Core_Document
     *
     * Initialisation de setaPDF
     */
    protected function init_file_setaPDF($doc, $path) {

        $reader = $path . $doc;
        $writer = new \SetaPDF_Core_Writer_File($path . "visa-" . $doc);
        // get a document instance
        $document = \SetaPDF_Core_Document::loadByFilename(
            $reader, $writer
        );

        return $document;
    }

    /**
     * @param $document
     *
     * Finalisation du document et affichage dans le navigateur
     */
    protected function finish_setaPDF($document) {

        // Show the the while page at a time
        $document->getCatalog()->setPageLayout(\SetaPDF_Core_Document_PageLayout::SINGLE_PAGE);

        // save and send it to the client
        $document->save()->finish();
    }

    /**
     * @param $doc
     * @param $first
     * @return string
     *
     * Retourne si la page est au format portrait ou paysage
     */
    protected function getFormatPdf($doc, $first = true) {

        $pages = $doc->getCatalog()->getPages();
        // as wee need to access all pages, ensure that they were resolved
        // ne peut pas etre appelé 2 fois
        //$pages->ensureAllPageObjects();

        // On détermine si c est la premiere ou la derniere page et on en recupere le format
        if($first) {
            $page = $pages->getPage(1);
        } else {
            $page = $pages->getLastPage();
        }
        $format = $page->getWidthAndHeight();
        if($format[0] < $format[1]) {
            $orientation = "portrait";
        } else {
            $orientation = "paysage";
        }
//        var_dump('orientation ', $orientation);
        return $orientation;

//        var_dump("============ Last Page  : ", $page->getWidthAndHeight(), '<br>');

    }

    /**
     * @param $absSign
     * @return mixed
     *
     * Fonctions permettant le calcul des valeurs x et y pour la signature et le visa
     */
    private function calcXSign($absSign) {

        return $translateXSign = ($absSign + 2) * 3.1;

    }

    private function calcYSign($ordSign) {

        return $translateYSign = ($ordSign + 10) * -2.9;

    }

    private function calcXVisa($absVisa) {

        return $translateXVisa = $absVisa * 2.9;

    }

    private function calcYVisa($ordVisa) {

        return $translateYVisa = $ordVisa* -2.9;

    }

    /**
     * @param $document
     * @param $classeurId
     * @param $translateX
     * @param $translateY
     * @param $first
     * @param $texteVisa
     * @param $color
     *
     * Création d un visa a apposer au document
     */
    protected function createVisa($document, $classeurId, $translateX, $translateY, $first, $texteVisa, $color) {
        // Params
        $borderWidth = 1;
        $borderWidthVisa = $borderWidth + 2;
        $padding = 7;
        $fontSize = 12;
        $texteStamp = '';

        if ($first) {
            $pagePosition = 'first';
        } else {
            $pagePosition = 'last';
        }
        // On recupere l orientation de la page portrait ou paysage
        // $format = $this->getFormatPdf($document, $first);

        // Params
        $translateX = $this->calcXVisa($translateX);
        $translateY = $this->calcYVisa($translateY);

        $em = $this->getEntityManager();
        $actions = $em->getRepository('SesileClasseurBundle:Action')->findBy(array(
            'classeur' => $classeurId,
            'action' => array('Validation', 'Classeur finalisé', 'Signature')
        ));

        foreach ($actions as $action) {
            // Trouver le bon utilisateur et recuperer sa qualite
            if($action->getUserAction() && $action->getUserAction()->getQualite()) {
                // Si la qualite + le nom de l utilisateur fait plus de 40 caractères on rajoute des sauts de ligne
                if(strlen($action->getUsername() . $action->getUserAction()->getQualite()) >= 40) {
                    $role = ",\n" . $action->getUserAction()->getQualite() . ",\n";
                } else {
                    $role = ", " . $action->getUserAction()->getQualite() . ", ";
                }
            }
            else {
                $role = '';
            }

            $texteStamp .= "\n" . $action->getUsername() . $role . ' le ' . $action->getDate()->format('d/m/Y à H:i');
        }

        // Color convert
        $colorRGB = $this->hex2rgb($color);
        $colorVisa = new \SetaPDF_Core_DataStructure_Color_Rgb($colorRGB[0] / 255, $colorRGB[1] / 255, $colorRGB[2] / 255);
        $colorVisaBorder = new \SetaPDF_Core_DataStructure_Color_Rgb(($colorRGB[0] + 80) / 255, ($colorRGB[1] + 80) / 255, ($colorRGB[2] + 80) / 255);


        // create a font object
        $font = \SetaPDF_Core_Font_Standard_Helvetica::create($document);

        // Stamp Visa
        $stamp_visa = new \SetaPDF_Stamper_Stamp_Text($font, $fontSize);
        $stamp_visa->setText($texteVisa);
        $stamp_visa->setAlign("center");
        $stamp_visa->setBorderWidth($borderWidthVisa);
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

        //$stamp_visa->setWidth($stamp->getWidth());
        $stamp_visa->setPaddingBottom($stamp->getHeight() - $fontSize + 3);

        $stamper->addStamp($stamp_visa, array(
//            'showOnPage' => '2-21',
            'showOnPage' => $pagePosition,
            'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
            'translateX' => $translateX + $padding,
            'translateY' => $translateY + $padding
        ));

        // On defini lequel des stamp est le plus large
        $stampWidth = $stamp->getWidth();
        $stamp_visaWidth = $stamp_visa->getWidth();
        if ($stampWidth >= $stamp_visaWidth) {
            $stamp_visa->setWidth($stampWidth);
        } else {
            $stamp_visa->setWidth($stamp_visaWidth + $padding);
            $stamp->setWidth($stamp_visaWidth - ($borderWidthVisa) * 2);
        }

        // stamp the document
        $stamper->stamp();
    }

    /**
     * @param $document
     * @param $translateX
     * @param $translateY
     * @param $first
     * @param $imageSignature
     * @param $user
     *
     * Creation d une signature a apposer au document
     */
    protected function createSignature($document, $translateX, $translateY, $first, $imageSignature, $user, $classeurId) {
        if ($first) {
//            $pagePosition = \SetaPDF_Stamper::PAGES_FIRST;
            $pagePosition = 'first';
        } else {
//            $pagePosition = \SetaPDF_Stamper::PAGES_LAST;
            $pagePosition = 'last';
        }

        // On recup les infos de la signature
        $em = $this->getEntityManager();
        $actions = $em->getRepository('SesileClasseurBundle:Action')->findBy(array(
            'classeur' => $classeurId,
            'action' => array('Signature')
        ));

        if ($actions) {
            $texteVisa = "";
            foreach ($actions as $action) {
                $texteVisa = "Signé électroniquement  le " . $action->getDate()->format('d/m/Y à H:i') . "\npar ";
            }
        }
        else {
            $texteVisa = "";
        }

        // On recupere l orientation de la page portrait ou paysage
        // $format = $this->getFormatPdf($document, $first);
        // Params
        $translateX = $this->calcXSign($translateX);
        $translateY = $this->calcYSign($translateY);

        $texteVisa .= $user->getPrenom(). " " . $user->getNom() . "\n" . $user->getQualite();
        // calcul du decalage en Y a cause de la qualite
        $translateYQuality = $translateY + 30 + 12 * substr_count($texteVisa, "\n");

//        $translateYQuality = ($translateYQuality >= -600) ? $translateYQuality : -600;

//        var_dump($translateYQuality); die();


        $fontSize = 10;
        // Color convert
        $colorRGB = $this->hex2rgb("#454545");
        $colorVisa = new \SetaPDF_Core_DataStructure_Color_Rgb($colorRGB[0] / 255, $colorRGB[1] / 255, $colorRGB[2] / 255);

        // create a font object
        $font = \SetaPDF_Core_Font_Standard_Helvetica::create($document);

        // Stamp Visa
        $stampTxt = new \SetaPDF_Stamper_Stamp_Text($font, $fontSize);
        $stampTxt->setText($texteVisa);
        $stampTxt->setAlign("left");
        $stampTxt->setTextColor($colorVisa);

        // create a stamper instance
        $stamperTxt = new \SetaPDF_Stamper($document);


        // add stamp to stamper on position left top for all pages with a specific translation
        $stamperTxt->addStamp($stampTxt, array(
            'showOnPage' => $pagePosition,
            'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
            'translateX' => $translateX,
            'translateY' => $translateYQuality
        ));

        // stamp the document
        $stamperTxt->stamp();

        // create a stamper instance for image
        $stamper = new \SetaPDF_Stamper($document);

        // get an image instance
        $image = \SetaPDF_Core_Image::getByPath($imageSignature);
        // initiate the stamp
        $stamp = new \SetaPDF_Stamper_Stamp_Image($image);
        // set height (and width until no setWidth is set the ratio will retain)
        $stamp->setWidth(150);

        // add stamp to stamper on position left top for all pages with a specific translation
        $stamper->addStamp($stamp, array(
            'showOnPage' => $pagePosition,
            'position' => \SetaPDF_Stamper::POSITION_LEFT_TOP,
            'translateX' => $translateX,
            'translateY' => $translateY
        ));

        // stamp the document
        $stamper->stamp();
    }

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
     * Affiche le fichier PDF avec le visa directement dans le navigateur
     *
     */
    public function setaPDFTamponVisa($doc, $classeurId, $translateX = 30, $translateY = -30, $first = true, $texteVisa, $color = false, $path) {

        $document = $this->init_setaPDF($doc,$path);

        $this->createVisa($document, $classeurId, $translateX, $translateY, $first, $texteVisa, $color);

        $this->finish_setaPDF($document);

    }

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
     * Affiche le fichier PDF avec le visa directement dans le navigateur
     *
     */
    public function setaPDFTamponVisaAll($doc, $classeurId, $translateX = 30, $translateY = -30, $first = true, $texteVisa, $color = false, $path) {

        $document = $this->init_file_setaPDF($doc, $path);

        $this->createVisa($document, $classeurId, $translateX, $translateY, $first, $texteVisa, $color);

        $this->finish_setaPDF($document);

    }

    /**
     * @param $doc
     * @param int $translateX
     * @param int $translateY
     * @param bool $first
     * @param string $imageSignature
     *
     * Affiche le fichier PDF avec la signature directement dans le navigateur
     */
    public function setaPDFTamponSignature($doc, $translateX = 30, $translateY = -30, $first = true, $imageSignature = '', $user, $classeurId, $path) {

        $document = $this->init_setaPDF($doc, $path);

        $this->createSignature($document, $translateX, $translateY, $first, $imageSignature, $user, $classeurId);

        $this->finish_setaPDF($document);
    }

    /**
     * @param $doc
     * @param int $translateX
     * @param int $translateY
     * @param bool $first
     * @param string $imageSignature
     *
     * Affiche le fichier PDF avec la signature directement dans le navigateur
     */
    public function setaPDFTamponSignatureAll($doc, $translateX = 30, $translateY = -30, $first = true, $imageSignature = '', $user, $classeurId, $path) {

        $document = $this->init_file_setaPDF($doc, $path);

        $this->createSignature($document, $translateX, $translateY, $first, $imageSignature, $user, $classeurId);

        $this->finish_setaPDF($document);
    }

    /**
     * @param $doc
     * @param $classeurId
     * @param int $translateXVisa
     * @param int $translateYVisa
     * @param int $translateXSign
     * @param int $translateYSign
     * @param bool $first
     * @param string $imageSignature
     * @param $texteVisa
     * @param bool $color
     *
     * Affiche le fichier PDF avec la signature et le visa directement dans le navigateur
     */
    public function setaPDFTamponALL($doc, $classeurId, $translateXVisa = 30, $translateYVisa = -30, $translateXSign = 30, $translateYSign = -30, $firstSign = true, $firstVisa = true, $imageSignature = '', $texteVisa, $color = false, $user, $path) {

        $document = $this->init_setaPDF($doc,$path);

        $this->createSignature($document, $translateXSign, $translateYSign, $firstSign, $imageSignature, $user, $classeurId);
        $this->createVisa($document, $classeurId, $translateXVisa, $translateYVisa, $firstVisa, $texteVisa, $color);

        $this->finish_setaPDF($document);
    }

    /**
     * @param $doc
     * @param $classeurId
     * @param int $translateXVisa
     * @param int $translateYVisa
     * @param int $translateXSign
     * @param int $translateYSign
     * @param bool $first
     * @param string $imageSignature
     * @param $texteVisa
     * @param bool $color
     *
     * Affiche le fichier PDF avec la signature et le visa directement dans le navigateur
     */
    public function setaPDFTamponALLFiles($doc, $classeurId, $translateXVisa = 30, $translateYVisa = -30, $translateXSign = 30, $translateYSign = -30, $firstSign = true, $firstVisa = true, $imageSignature = '', $texteVisa, $color = false, $user, $path) {

        $document = $this->init_file_setaPDF($doc, $path);

        $this->createSignature($document, $translateXSign, $translateYSign, $firstSign, $imageSignature, $user, $classeurId);
        $this->createVisa($document, $classeurId, $translateXVisa, $translateYVisa, $firstVisa, $texteVisa, $color);

        $this->finish_setaPDF($document);
    }

    /*public function affPDF($doc, $classeurId) {

    }*/

    /**
     * @param $hex
     * @return array
     *
     * Convertit une couleur #001122 en RGB
     */
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

}
