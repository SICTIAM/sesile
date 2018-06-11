<?php


namespace Sesile\MigrationBundle\Tests\Migrator;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MigrationBundle\Migrator\UserMigrator;
use Sesile\MigrationBundle\Service\LegacyUserService;
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
        $expectedUsersCollection = $this->userService->getLegacyUsersByCollectivity($oldCollectivityId);
        $this->assertUsers($expectedUsersCollection, $result->getData());
        /**
         * assert User Roles
         */
        $userOne = $result->getData()[0];
        self::assertCount(2, $userOne->getUserrole());
        self::assertEquals('Directeur Général', $userOne->getUserrole()[0]->getUserRoles());
        self::assertEquals('Agent administratif', $userOne->getUserrole()[1]->getUserRoles());
    }

    private function assertUsers($expectedUsersCollection, $usersCollection)
    {
        /**
         * USER 1
         */
        $user1 = $usersCollection[0];
        $expectedUser1 = $expectedUsersCollection[0];
        self::assertEquals($expectedUser1['username'], $user1->getUsername());
        self::assertEquals($expectedUser1['username_canonical'], $user1->getUsernameCanonical());
        self::assertEquals($expectedUser1['email'], $user1->getEmail());
        self::assertEquals($expectedUser1['email_canonical'], $user1->getEmailCanonical());
        self::assertTrue($user1->isEnabled());
        self::assertEquals($expectedUser1['salt'], $user1->getSalt());
        self::assertEquals($expectedUser1['password'], $user1->getPassword());
        self::assertEquals($expectedUser1['confirmation_token'], $user1->getConfirmationToken());
        self::assertEquals($expectedUser1['password_requested_at'], $user1->getPasswordRequestedAt());
        self::assertEquals(['ROLE_SUPER_ADMIN', 'ROLE_USER'], $user1->getRoles());
//        self::assertEquals('a:1:{i:0;s:10:"ROLE_ADMIN";}', serialize($user1->getRoles()));
//        self::assertEquals($expectedUser1['roles'], serialize($user1->getRoles()));
        self::assertEquals($expectedUser1['Nom'], $user1->getNom());
        self::assertEquals($expectedUser1['Prenom'], $user1->getPrenom());
        self::assertEquals($expectedUser1['path'], $user1->getPath());
        self::assertEquals($expectedUser1['ville'], $user1->getVille());
        self::assertEquals($expectedUser1['code_postal'], $user1->getCp());
        self::assertEquals($expectedUser1['pays'], $user1->getPays());
        self::assertEquals($expectedUser1['departement'], $user1->getDepartement());
        self::assertEquals($expectedUser1['role'], $user1->getRole());
        self::assertEquals($expectedUser1['apitoken'], $user1->getApitoken());
        self::assertEquals($expectedUser1['apisecret'], $user1->getApisecret());
        self::assertTrue($user1->getApiactivated());
        self::assertEquals($expectedUser1['pathSignature'], $user1->getPathSignature());
        self::assertEquals($expectedUser1['qualite'], $user1->getQualite());
        self::assertEquals('4.0', $user1->getSesileVersion());
        self::assertNull($user1->getOzwilloId());
        /**
         * USER 2
         */
        $user1 = $usersCollection[1];
        $expectedUser1 = $expectedUsersCollection[1];
        self::assertEquals($expectedUser1['username'], $user1->getUsername());
        self::assertEquals($expectedUser1['username_canonical'], $user1->getUsernameCanonical());
        self::assertEquals($expectedUser1['email'], $user1->getEmail());
        self::assertEquals($expectedUser1['email_canonical'], $user1->getEmailCanonical());
        self::assertTrue($user1->isEnabled());
        self::assertEquals($expectedUser1['salt'], $user1->getSalt());
        self::assertEquals($expectedUser1['password'], $user1->getPassword());
        self::assertEquals($expectedUser1['confirmation_token'], $user1->getConfirmationToken());
        self::assertEquals($expectedUser1['password_requested_at'], $user1->getPasswordRequestedAt());
        self::assertEquals(['ROLE_USER'], $user1->getRoles());
//        self::assertEquals('a:1:{i:0;s:10:"ROLE_ADMIN";}', serialize($user1->getRoles()));
//        self::assertEquals($expectedUser1['roles'], serialize($user1->getRoles()));
        self::assertEquals($expectedUser1['Nom'], $user1->getNom());
        self::assertEquals($expectedUser1['Prenom'], $user1->getPrenom());
        self::assertEquals($expectedUser1['path'], $user1->getPath());
        self::assertEquals($expectedUser1['ville'], $user1->getVille());
        self::assertEquals($expectedUser1['code_postal'], $user1->getCp());
        self::assertEquals($expectedUser1['pays'], $user1->getPays());
        self::assertEquals($expectedUser1['departement'], $user1->getDepartement());
        self::assertEquals($expectedUser1['role'], $user1->getRole());
        self::assertEquals($expectedUser1['apitoken'], $user1->getApitoken());
        self::assertEquals($expectedUser1['apisecret'], $user1->getApisecret());
        self::assertTrue($user1->getApiactivated());
        self::assertEquals($expectedUser1['pathSignature'], $user1->getPathSignature());
        self::assertEquals($expectedUser1['qualite'], $user1->getQualite());
        self::assertEquals('4.0', $user1->getSesileVersion());
        self::assertNull($user1->getOzwilloId());
    }


}