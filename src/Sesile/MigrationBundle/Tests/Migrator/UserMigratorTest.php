<?php


namespace Sesile\MigrationBundle\Tests\Migrator;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class UserMigratorTest extends LegacyWebTestCase
{
    /**
     * @var UserMigrator
     */
    protected $userMigrator;
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;
    /**
     * @var LegacyUserService
     */
    protected $userService;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class
            ]
        )->getReferenceRepository();
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        $this->userMigrator = $this->getContainer()->get('user.migrator');
        $this->userService = $this->getContainer()->get('legacy.user.service');
        parent::setUp();
    }

    public function testUserMigration()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $oldCollectivityId = 1;
        $result = $this->userMigrator->migrate($collectivity, $oldCollectivityId);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertCount(2, $result->getData());
        $expectedUsersCollection = $this->userService->getLegacyUsers($oldCollectivityId);
    }


}