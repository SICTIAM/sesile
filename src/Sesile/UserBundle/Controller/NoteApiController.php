<?php

namespace Sesile\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sesile\UserBundle\Entity\Note;
use Sesile\UserBundle\Form\NoteType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Rest\Route("/apirest/note", options = { "expose" = true })
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class NoteApiController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @Rest\View(serializerGroups={"listNote"})
     * @Rest\Get("s/")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('SesileUserBundle:Note')->findBy(
            array(),
            array('created' => 'DESC')
        );

        return $notes;
    }

    /**
     * @Rest\View(serializerGroups={"getLastNote"})
     * @Rest\Get("/last")
     */
    public function getLastAction()
    {
        $em = $this->getDoctrine()->getManager();
        $note =
            $em->getRepository('SesileUserBundle:Note')
                ->findOneBy(
                    array(),
                    array('created' => 'DESC'));

        $alreadyOpen = false;

        if ($note && !empty($note->getUsers())) {
            foreach ($note->getUsers() as $user) {
                if ($user->getId() == $this->getUser()->getId()) {
                    $alreadyOpen = true;
                }
            }
        }

        return array(
            'alreadyOpen' => $alreadyOpen,
            'note' => $note
        );
    }

    /**
     * @Rest\View(serializerGroups={"detailsNote"})
     * @Rest\Get("/{id}")
     * @ParamConverter("Note", options={"mapping": {"id": "id"}})
     * @param Note $note
     * @return Note
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getIdAction(Note $note)
    {
        return $note;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/")
     * @param Request $request
     * @return JsonResponse
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function postAction(Request $request) {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            return $note;
        } else {
            return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Post("/readed/{id}")
     * @ParamConverter("note", options={"mapping": {"id": "id"}})
     * @param Note $note
     * @return Note
     */
    public function readedAction(Note $note) {

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $note->addUser($user);
        $user->addNote($note);

        $em->flush();

        return $note;
    }

    /**
     * @Rest\View()
     * @Rest\Put("/{id}")
     * @ParamConverter("note", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Note $note
     * @return Note
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function updateAction(Request $request, Note $note) {
        $form = $this->createForm(NoteType::class, $note);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($note);
            $em->flush();
        }

        return $note;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/{id}")
     * @ParamConverter("note", options={"mapping": {"id": "id"}})
     * @param Note $note
     * @return array|Note[]|JsonResponse
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function removeAction (Note $note) {
        if (empty($note)) {
            return new JsonResponse('', Response::HTTP_NOT_FOUND);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        $notes = $this->getDoctrine()
            ->getManager()
            ->getRepository('SesileUserBundle:Note')
            ->findAll();

        return $notes;
    }
}