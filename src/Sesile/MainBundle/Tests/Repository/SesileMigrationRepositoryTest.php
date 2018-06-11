<?php


namespace Sesile\MainBundle\Tests\Repository;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\Entity\SesileMigration;
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
                SesileMigrationFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testGetCollectiviteList()
    {
        $result = $this->em->getRepository(SesileMigration::class)->testGetSesileMigrationHistory();
        self::assertCount(2, $result);
    }

}