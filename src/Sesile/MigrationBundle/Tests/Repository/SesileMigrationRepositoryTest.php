<?php


namespace Sesile\MigrationBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MigrationBundle\Entity\SesileMigration;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class SesileMigrationRepositoryTest extends SesileWebTestCase
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
                SesileMigrationFixtures::class,
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testGetSesileMigrationHistory()
    {
        $result = $this->em->getRepository(SesileMigration::class)->getSesileMigrationHistory();
        self::assertCount(3, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertArrayHasKey('collectivityId', $result[0]);
        self::assertArrayHasKey('collectivityName', $result[0]);
        self::assertArrayHasKey('siren', $result[0]);
        self::assertArrayHasKey('status', $result[0]);
        self::assertArrayHasKey('usersExported', $result[0]);
        self::assertArrayHasKey('oldId', $result[0]);
        self::assertArrayHasKey('date', $result[0]);
        self::assertArrayHasKey('instanceId', $result[0]);
        self::assertArrayHasKey('serviceId', $result[0]);
    }

}