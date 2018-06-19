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

    public function testSwitchColectivityId()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $collectivityOneOzwillo = $collectivityOne->getOzwillo();
        self::assertInstanceOf(CollectiviteOzwillo::class, $collectivityOneOzwillo);
        $collectivityThree = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        $collectivityThreeOzwillo = $collectivityThree->getOzwillo();
        self::assertNull($collectivityThreeOzwillo);
        $ozwilloInstanceId = $collectivityOne->getOzwillo()->getInstanceId();

        $result = $this->em->getRepository(CollectiviteOzwillo::class)->switchCollectivityId($collectivityOne->getId(), $collectivityThree->getId());
        self::assertTrue($result);
        /**
         * check DB
         */
        $res = $this->em->getRepository(CollectiviteOzwillo::class)->findBy(['collectivite' => $collectivityOne]);
        self::assertCount(0, $res);
        $res = $this->em->getRepository(CollectiviteOzwillo::class)->findBy(['collectivite' => $collectivityThree]);
        self::assertCount(1, $res);
        self::assertEquals($ozwilloInstanceId, $res[0]->getInstanceId());
    }

    public function testSwitchColectivityIdShouldReturnFalseWhenUknowsCollectivityIdIsUsed()
    {
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $collectivityOneOzwillo = $collectivityOne->getOzwillo();
        self::assertInstanceOf(CollectiviteOzwillo::class, $collectivityOneOzwillo);

        $result = $this->em->getRepository(CollectiviteOzwillo::class)->switchCollectivityId($collectivityOne->getId(), 'uknown');
        self::assertFalse($result);
        /**
         * check DB
         */
        $res = $this->em->getRepository(CollectiviteOzwillo::class)->findBy(['collectivite' => $collectivityOne]);
        self::assertCount(1, $res);
    }

    public function testUpdateNotifiedToKernel()
    {

        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $result = $this->em->getRepository(CollectiviteOzwillo::class)->updateNotifiedToKernel($collectivityOne->getId(), true);
        self::assertTrue($result);
        /**
         * check DB
         */
        $res = $this->em->getRepository(CollectiviteOzwillo::class)->findOneBy(['collectivite' => $collectivityOne]);
        self::assertTrue($res->getNotifiedToKernel());
    }
}