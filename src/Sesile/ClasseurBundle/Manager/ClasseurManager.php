<?php


namespace Sesile\ClasseurBundle\Manager;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\ClasseurBundle\Domain\SearchClasseurData;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\User;

/**
 * Class ClasseurManager
 * @package Sesile\ClasseurBundle\Manager
 */
class ClasseurManager
{
    const ACTION_ADD_DOCUMENT = 'Ajout du document';
    const ACTION_DELETE_DOCUMENT = 'Suppression du document';
    const ACTION_DEPOSIT_CLASSEUR = 'Dépot du classeur';
    const ACTION_SIGN = 'Signature';
    const ACTION_VALIDATION_CLASSEUR = 'Validation';
    const ACTION_FINISH_CLASSEUR = 'Classeur finalisé';
    const ACTION_REMOVE_CLASSEUR = 'Classeur retiré';
    const ACTION_SIGN_CLASSEUR = 'Classeur signé';
    const ACTION_RE_DEPOSIT_CLASSEUR = 'Classeur à nouveau soumis';
    const ACTION_REFUSED = 'Refus';
    const ACTION_RETRACT = 'Rétractation';
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CollectiviteManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Get an array of all organisations (aka collectivité)
     *
     * @return Message
     */
    public function searchClasseurs(Collectivite $collectivity, User $user, SearchClasseurData $classeurData)
    {
        try {
            $data = $this->em->getRepository(Classeur::class)->searchClasseurs($collectivity->getId(), $user->getId(), $classeurData->getName());

            return new Message(true, $data);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[ClasseurManager]/searchClasseurs error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

    /**
     * @param Classeur $classeur
     * @param User $user
     * @param $actionLabel
     * @param string $commentaire
     *
     * @return Message
     */
    public function addClasseurAction(Classeur $classeur, User $user, $actionLabel, $commentaire = '')
    {
        try {
            // Ajout d'une action pour le classeur
            $action = new Action();
            $action->setCommentaire($commentaire);
            $action->setClasseur($classeur);
            $action->setUser($user);
            $action->setAction($actionLabel);
            $this->em->persist($action);
            $this->em->flush();

            return new Message(true, $action);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[ClasseurManager]/addClasseurAction error: %s', $e->getMessage()));

            return new Message(false, null, [$e->getMessage()]);
        }
    }

}