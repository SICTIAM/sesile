<?php


namespace Sesile\UserBundle\Tests\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\UserBundle\Entity\User;

class CollectiviteOzwilloRepositoryTest extends WebTestCase
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
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
            ]
        )->getReferenceRepository();
    }

    public function testUserHasOzwilloCollectivityShouldReturnTrue()
    {
        $user = $this->fixtures->getReference('user-one');
        $collectivity = $this->fixtures->getReference('collectivite-one');
        $ozwilloCollectivityClientId = $collectivity->getOzwillo()->getClientId();
        $result = $this->em->getRepository(CollectiviteOzwillo::class)->userHasOzwilloCollectivity(
            $user->getId(),
            $ozwilloCollectivityClientId
        );
        self::assertTrue($result);
    }

    public function testUserHasOzwilloCollectivityShouldReturnFalseIfNoCollectivityFoundForUser()
    {
        $user = $this->fixtures->getReference('user-two');
        $collectivity = $this->fixtures->getReference('collectivite-two');
        $ozwilloCollectivityClientId = $collectivity->getOzwillo()->getClientId();
        $result = $this->em->getRepository(CollectiviteOzwillo::class)->userHasOzwilloCollectivity(
            $user->getId(),
            $ozwilloCollectivityClientId
        );
        self::assertFalse($result);
    }
}