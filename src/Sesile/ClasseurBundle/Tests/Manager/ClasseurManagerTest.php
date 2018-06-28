<?php


namespace Sesile\ClasseurBundle\Tests\Manager;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\ClasseurBundle\Domain\SearchClasseurData;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\ClasseurBundle\Entity\ClasseurRepository;
use Sesile\ClasseurBundle\Manager\ClasseurManager;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

class ClasseurManagerTest extends SesileWebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ClasseurManager
     */
    protected $classeurManager;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
                TypeClasseurFixtures::class,
                UserPackFixtures::class,
                CircuitValidationFixtures::class,
                ClasseurFixtures::class,
            ]
        )->getReferenceRepository();
        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->classeurManager = new ClasseurManager($this->em, $this->logger);
    }

    public function testSearchClasseur()
    {
        $repository = $this->createMock(ClasseurRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Classeur::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('searchClasseurs')
            ->willReturn(
                [
                    ['id' => 12345, 'nom' => 'Sictiam Classeur', 'description' => 'sictiam'],
                    ['id' => 54122, 'nom' => 'toto Classeur', 'description' => 'sictiam']
                ]
            );
        $collectivity = CollectiviteFixtures::aValidCollectivite();
        $user = UserFixtures::aValidUser();
        $data = new SearchClasseurData('Classeur');
        $result = $this->classeurManager->searchClasseurs($collectivity, $user, $data);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
    }

    public function testAddClasseurAction()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $classeurManager = new ClasseurManager($em, $this->logger);
        $classeur = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE);
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $action = ClasseurManager::ACTION_REFUSED;
        $result = $classeurManager->addClasseurAction($classeur, $user, ClasseurManager::ACTION_REFUSED, 'commentaire');
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        $classeurManager->addClasseurAction($classeur, $user, ClasseurManager::ACTION_RE_DEPOSIT_CLASSEUR, 'commentaire');
        /**
         * check DB
         */
        $em->clear();
        $res = $em->getRepository(Action::class)->find($result->getData()->getId());
        self::assertInstanceOf(Action::class, $res);
        self::assertEquals($action, $res->getAction());
        self::assertEquals($user->getPrenom() . ' ' . $user->getNom(), $res->getUsername());
        self::assertEquals($user->getId(), $res->getUserAction()->getId());
        self::assertEquals('commentaire', $res->getCommentaire());
        $res = $em->getRepository(Classeur::class)->find($classeur->getId());
        self::assertCount(2, $res->getActions());
    }

}