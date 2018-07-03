<?php


namespace Sesile\ClasseurBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\DocumentFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class ClasseurRepositoryTest extends SesileWebTestCase
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
        parent::setUp();
    }

    public function tearDown()
    {
        $this->em->getConnection()->close();
    }

    /**
     * this test will search the classeurs of the user one by the term "Classeur"
     * the fixtures create 3 classeurs where two for the user one
     * Fixture Classeur && Another Classeur but one only is on the desired collectivity
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function testSearchClasseurs()
    {
        //insert a new classeur
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $classeur = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE);
        $searchTerm = 'Classeur';//Fixture Classeur
        $result = $this->em->getRepository(Classeur::class)->searchClasseurs(
            $collectivity->getId(),
            $user->getId(),
            $searchTerm
        );
        self::assertCount(1, $result);
        self::assertEquals($classeur->getNom(), $result[0]['nom']);
        self::assertEquals($classeur->getDescription(), $result[0]['description']);
        self::assertEquals($classeur->getId(), $result[0]['id']);
    }
    public function testSearchClasseursShouldReturnEmptyArrayIfNothingFound()
    {
        //insert a new classeur
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $searchTerm = 'Nothing to find';//Fixture Classeur
        $result = $this->em->getRepository(Classeur::class)->searchClasseurs(
            $collectivity->getId(),
            $user->getId(),
            $searchTerm
        );
        self::assertCount(0, $result);
    }

    public function testGetExpiredClasseurs()
    {
        //change crateion date of classeurs
        $classeur = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE);
        $sql = sprintf("update Classeur set creation = '2018-01-02' where id=%s", $classeur->getId());
        $this->em->getConnection()->executeQuery($sql);
        $classeur2 = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE_TWO);
        $sql = sprintf("update Classeur set creation = '2017-01-02' where id=%s", $classeur2->getId());
        $this->em->getConnection()->executeQuery($sql);

        $result = $this->em->getRepository(Classeur::class)->getExpiredClasseurs();
        self::assertCount(2, $result);
        self::assertArraySubset([$classeur->getId(), $classeur2->getId()], $result);
    }

}