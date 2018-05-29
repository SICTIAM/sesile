<?php


namespace Sesile\UserBundle\Tests\Manager;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\GroupeRepository;
use Sesile\UserBundle\Manager\CircuitDeValidationManager;

/**
 * Class CircuitDeValidationManagerTest
 * @package Sesile\UserBundle\Tests\Manager
 */
class CircuitDeValidationManagerTest extends SesileWebTestCase
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
     * @var CircuitDeValidationManager
     */
    protected $circuitDeValidationManager;

    public function setUp()
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->circuitDeValidationManager = new CircuitDeValidationManager($this->em, $this->logger);
    }

    public function testGetCircuitDataByUserAndCollectivite()
    {
        $repository = $this->createMock(GroupeRepository::class);
        $this->em->expects(self::once())
            ->method('getRepository')
            ->with(Groupe::class)
            ->willReturn($repository);

        $repository->expects(self::once())
            ->method('getCircuitDataByUserAndCollectivite')
            ->willReturn(
                [
                    [
                        'circuitId' => "135",
                        'circuitName' => "Circuit de validation",
                        'typeName' => "The type",
                        'typeId' => "156",
                    ],
                    [
                        'circuitId' => "135",
                        'circuitName' => "Circuit de validation",
                        'typeName' => "new type",
                        'typeId' => "250",
                    ],
                    [
                        'circuitId' => "200",
                        'circuitName' => "Circuit sictiam",
                        'typeName' => "new type",
                        'typeId' => "250",
                    ],
                ]
            );
        $collectivite = CollectiviteFixtures::aValidCollectivite();
        $result = $this->circuitDeValidationManager->getCircuitDataByUserAndCollectivite('userID', $collectivite);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        $expectedData = [
            [
                'id' => "135",
                'nom' => "Circuit de validation",
                'type_classeur' => ["156", "250"],
            ],
            [
                'id' => "200",
                'nom' => "Circuit sictiam",
                'type_classeur' => ["250"],
            ],
        ];
        self::assertArraySubset($expectedData, $result->getData());
    }

}