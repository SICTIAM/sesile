<?php


namespace Sesile\UserBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\Groupe;

class GroupeRepositoryTest extends SesileWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
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
    }

    public function testGetCircuitDataByUserAndCollectivite()
    {
        $user = $this->fixtures->getReference('user-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $result = $this->em->getRepository(Groupe::class)->getCircuitDataByUserAndCollectivite($user->getEmail(), $collectivite->getId());
        self::assertCount(1, $result);
        var_dump($result);
        $expectedCircuit = $this->fixtures->getReference(CircuitValidationFixtures::CIRCUIT_VALIDATION_REFERENCE);
        $expectedType = $this->fixtures->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE);
        $expected[] = [
            'circuitId' => $expectedCircuit->getId(),
            'circuitName' => $expectedCircuit->getNom(),
            'typeName' => $expectedType->getNom(),
            'typeId' => $expectedType->getId(),
        ];
        self::assertArraySubset($expected, $result);
    }

}