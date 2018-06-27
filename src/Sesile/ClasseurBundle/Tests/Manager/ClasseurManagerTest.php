<?php


namespace Sesile\ClasseurBundle\Tests\Manager;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\ClasseurBundle\Domain\SearchClasseurData;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\ClasseurBundle\Entity\ClasseurRepository;
use Sesile\ClasseurBundle\Manager\ClasseurManager;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

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

    public function setUp()
    {
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

}