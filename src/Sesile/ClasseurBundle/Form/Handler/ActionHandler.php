<?php
namespace Sesile\ClasseurBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
/**
 * Description of ExerciceHandler
 *
 * @author abdel
 */
class ActionHandler
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $manager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    public function handle(FormInterface $form, Request $request)
    {
        if ($form->isValid()) {
            $data = $form->getData();
            $observation = $request->request->get('observation');
            $data->setObservation($observation);

            var_dump($observation);
            $this->manager->persist($data);
            $this->manager->flush();
            $route = 'classeur';

            return new RedirectResponse($this->router->generate($route));
        }
        return $form;
    }
}
