<?php
namespace Sesile\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\ClasseurBundle\Form\ClasseurType;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\ClasseursUsers;

/**
 * Class DocumentController
 * @package Sesile\ApiBundle\Controller
 *
 * @Route("/document")
 */
class DocumentController extends FOSRestController implements TokenAuthenticatedController
{


    /**
     * Cette méthode permet de récupérer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Permet de récupérer un document",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Id du document à obtenir"}
     *  }
     * )
     */
    public function getAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $document = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($document)) {
            throw new AccessDeniedHttpException("le document " . $id . " n'existe pas !");
        }
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($document->getClasseur(), $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès au classeur auquel appartient le document " . $id);
        }


        return $document;

    }


    /**
     * Cette méthode permet d'editer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/edit")
     * @Rest\View()
     * @Method("post")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet d'editer un document",
     *   parameters={
     *
     *
     *          {"name"="signed", "dataType"="integer", "format"="1 si le fichier transmis à été signé numériquement, 0 sinon", "required"=false, "description"="Etat de signature du fichier"},
     *
     *          {"name"="attachment : file", "dataType"="file", "required"=false, "description"="Fichier associé, passé en attachment à la manière d'un formulaire web (récupérable en php par $_FILE['file'])"}
     *  }
     * )
     **/
    public function updateAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));

        $document = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($document)) {
            throw new AccessDeniedHttpException("le document " . $id . " n'existe pas !");
        }
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($document->getClasseur()->getId(), $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès au classeur auquel appartient le document " . $id);
        }


        // obtenir une instance de UploadedFile identifiée par file

        if ($request->files->has('file')) {
            $file = $request->files->get('file');
            $name = $file->getClientOriginalName();
            $movedfile = $file->move($this->get('kernel')->getRootDir() . '/../web/uploads/docs/', uniqid() . '.' . $file->getExtension());
            unlink($this->get('kernel')->getRootDir() . '/../web/uploads/docs/' . $document->getRepoUrl());
            $document->setRepourl($movedfile->getBasename());
            $document->setType($movedfile->getMimeType());
            $document->setName($name);
        }


        if ($request->request->has('signed')) {
            $document->setName(($request->request->get('signed') == 1) ? true : false);
        }


        $action = new Action();
        $action->setClasseur($classeur[0]);
        $action->setUser($user);
        $action->setAction("Modification du document " . $document->getName());
        $em->persist($action);


        $em->flush();
        $em->getRepository('SesileDocumentBundle:DocumentHistory')->writeLog($document, "Modification du document", null);

        return $document;


    }


    /**
     * Cette méthode permet de supprimer un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}")
     * @Rest\View()
     * @Method("delete")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de supprimer un document",
     *  requirements={
     *
     *  }
     * )
     */
    public function deleteAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $document = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($document)) {
            throw new AccessDeniedHttpException("le document " . $id . " n'existe pas !");
        }
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($document->getClasseur(), $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès au classeur auquel appartient le document " . $id);
        }


        $action = new Action();
        $action->setClasseur($classeur[0]);
        $action->setUser($user);
        $action->setAction("Suppression du document " . $document->getName());
        $em->persist($action);
        $em->flush();
        $em->remove($document);
        $em->flush();


        return array('code' => '200', 'message' => 'Document supprimé');;


    }

    /**
     * Cette méthode permet récupérer le contenu d'un document
     *
     * Si l'utilisateur courant n'as pas accès au classeur associé au document, un 403 not allowed sera renvoyé
     *
     * @var Request $request
     * @return array
     * @Route("/{id}/content")
     * @Rest\View()
     * @Method("get")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Permet de récupérer le contenu d'un document",
     *  requirements={
     *
     *  }
     * )
     */
    public function downloadAction(Request $request, $id)
    {


        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository('SesileUserBundle:User')->findOneBy(array('apitoken' => $request->headers->get('token'), 'apisecret' => $request->headers->get('secret')));
        $document = $em->getRepository('SesileDocumentBundle:Document')->findOneById($id);
        if (empty($document)) {
            throw new AccessDeniedHttpException("le document " . $id . " n'existe pas !");
        }
        $classeur = $em->getRepository('SesileClasseurBundle:ClasseursUsers')->getClasseurByUser($document->getClasseur(), $user->getId());


        if (empty($classeur[0])) {
            throw new AccessDeniedHttpException("Vous n'avez pas accès au classeur auquel appartient le document " . $id);
        }

        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type('uploads/docs/' . $document->getRepourl()));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $document->getName() . '"');
        $response->headers->set('Content-Length', filesize('uploads/docs/' . $document->getRepourl()));


        // $response->sendHeaders();


        $response->setContent(file_get_contents('uploads/docs/' . $document->getRepourl()));


        return $response;


    }


}