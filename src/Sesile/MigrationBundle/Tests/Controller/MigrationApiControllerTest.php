<?php

namespace Sesile\MigrationBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\SesileMigrationFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;
use Sesile\MigrationBundle\Domain\MigrationReport;
use Sesile\MigrationBundle\Entity\SesileMigration;
use Sesile\MigrationBundle\Manager\SesileMigrationManager;
use Sesile\MigrationBundle\Migrator\OzwilloUserMigrator;
use Sesile\MigrationBundle\Tests\LegacyWebTestCase;

class MigrationApiControllerTest extends LegacyWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var SesileMigrationManager
     */
    protected $sesileMigrationManager;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
                SesileMigrationFixtures::class,
            ]
        )->getReferenceRepository();
        $this->resetLegacyTestDatabase();
        $this->loadLegacyFixtures();
        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->sesileMigrationManager = $this->getContainer()->get('sesile_migration.manager');
        parent::setUp();
    }

    public function testGetCollectivityListShouldFailIfNotLoggedIn()
    {
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(302, $this->client);
    }

    public function testGetCollectivityListShouldFailWithAccessDeniedIfNotRoleSuperAdmin()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(403, $this->client);
    }

    public function testGetCollectivityList()
    {
        //remove sesile migration fixtures
        $sesileMigration = $this->fixtures->getReference(SesileMigrationFixtures::SESILE_MIGRATION_ONE_REFERENCE);
        $this->em->remove($sesileMigration);
        $sesileMigration = $this->fixtures->getReference(SesileMigrationFixtures::SESILE_MIGRATION_TWO_REFERENCE);
        $this->em->remove($sesileMigration);
        $sesileMigration = $this->fixtures->getReference(SesileMigrationFixtures::SESILE_MIGRATION_THREE_REFERENCE);
        $this->em->remove($sesileMigration);
        $this->em->flush();
        $this->em->clear();
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $content);
        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        self::assertEquals($collectivityOne->getId(), $content[0]['id']);
        self::assertEquals($collectivityOne->getNom(), $content[0]['nom']);
        self::assertEquals($collectivityOne->getDomain(), $content[0]['domain']);
        $collectivityTwo = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        self::assertEquals($collectivityTwo->getId(), $content[1]['id']);
        self::assertEquals($collectivityTwo->getNom(), $content[1]['nom']);
        self::assertEquals($collectivityTwo->getDomain(), $content[1]['domain']);
        $collectivityThree = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_THREE_REFERENCE);
        self::assertEquals($collectivityThree->getId(), $content[2]['id']);
        self::assertEquals($collectivityThree->getNom(), $content[2]['nom']);
        self::assertEquals($collectivityThree->getDomain(), $content[2]['domain']);
    }

    public function testGetCollectivityListShouldExludeTheCollectivityInSesileMigration()
    {

        $sesileMigration = $this->fixtures->getReference(SesileMigrationFixtures::SESILE_MIGRATION_TWO_REFERENCE);
        $this->em->remove($sesileMigration);
        $sesileMigration = $this->fixtures->getReference(SesileMigrationFixtures::SESILE_MIGRATION_THREE_REFERENCE);
        $this->em->remove($sesileMigration);
        $this->em->flush();
        $this->em->clear();
//        $collectivityOne = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
//        $this->persistSesileMigration($collectivityOne);

        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/collectivity/list');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $content);
        $collectivityTwo = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE);
        self::assertEquals($collectivityTwo->getId(), $content[0]['id']);
        self::assertEquals($collectivityTwo->getNom(), $content[0]['nom']);
        self::assertEquals($collectivityTwo->getDomain(), $content[0]['domain']);
    }

    public function testCheckCollectivitySirenShouldSucceedIfSirenIsAvailable()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', '/api/migration/v3v4/org/check/siren/FR1212121');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //{success: 1, siren: "21121"}
        self::assertEquals(1, $content['success']);
        self::assertEquals('FR1212121', $content['siren']);
    }

    public function testCheckCollectivitySirenShouldFailIfSirenIsNotAvailable()
    {
        $collectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $user = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($user);
        $this->client->request('GET', sprintf('/api/migration/v3v4/org/check/siren/%s', $collectivity->getSiren()));
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //{success: 0, siren: "21121", orgName : "nom de la collectivité avec le SIREN" }
        self::assertEquals(0, $content['success']);
        self::assertEquals($collectivity->getSiren(), $content['siren']);
        self::assertEquals($collectivity->getNom(), $content['orgName']);
    }

    /**
     * Test l'action lors on selection une collectivité à migrer, qui n'est pas encore provisioné par ozwillo
     *
     */
    public function testMigrateCollectivityShouldSucceedForNotProvisionedCollectivity()
    {
        $collectivity = $this->persistCollectivity();
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $this->client->enableProfiler();
        $postData = [
            'orgId' => $collectivity->getId(),
            'siren' => '784512658',
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(201, $this->client);
        /**
         * check DB
         */
        $this->em->clear();
        $testCollectivity = $this->em->getRepository(Collectivite::class)->find($collectivity->getId());
        self::assertEquals('784512658', $testCollectivity->getSiren());
        self::assertNull($testCollectivity->getOzwillo());
        $testSesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
            ['collectivityId' => $collectivity->getId()]
        );
        self::assertEquals('784512658', $testSesileMigration->getSiren());
        self::assertEquals(SesileMigration::STATUS_EN_COURS, $testSesileMigration->getStatus());
        self::assertEquals($collectivity->getNom(), $testSesileMigration->getCollectivityName());
        self::assertFalse($testSesileMigration->hasUsersExported());
    }

    /**
     * Test l'action lors on selection une collectivité à migrer,
     * avec un SIREN qui apartienne à une collectivité qui a été provisioné par ozwillo
     * sur sesile.
     * Sesile doit recuperer la collectiviteOzwillo et l'attacher à la collectivité en question.
     *
     */
    public function testMigrateCollectivityAlreadyProvisioned()
    {
        $provisionedCollectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $collectivity = $this->persistCollectivity();
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $this->client->enableProfiler();
        $siren = $provisionedCollectivity->getSiren();
        $postData = [
            'orgId' => $collectivity->getId(),
            'siren' => $siren,
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(201, $this->client);
        /**
         * check DB
         */
        $this->em->clear();
        $testCollectivity = $this->em->getRepository(Collectivite::class)->find($collectivity->getId());
        self::assertEquals($siren, $testCollectivity->getSiren());
        self::assertInstanceOf(CollectiviteOzwillo::class, $testCollectivity->getOzwillo());
        /**
         * la collectivité en doublon déjà provisioné doit disparaitre
         * soit perdre la collectivité ozwillo
         * et aussi doit perdre son siren
         */
        $oldCollectivity = $this->em->getRepository(Collectivite::class)->find($provisionedCollectivity->getId());
        self::assertNull($oldCollectivity);
//        self::assertNull($oldCollectivity->getOzwillo());
//        self::assertNull($oldCollectivity->getSiren());
//        //mur remove all Ref collectivity users
//        self::assertCount(0, $oldCollectivity->getUsers());

        $testSesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
            ['collectivityId' => $collectivity->getId()]
        );
        self::assertEquals($siren, $testSesileMigration->getSiren());
        self::assertEquals(SesileMigration::STATUS_EN_COURS, $testSesileMigration->getStatus());
        self::assertEquals($collectivity->getNom(), $testSesileMigration->getCollectivityName());
        self::assertFalse($testSesileMigration->hasUsersExported());
    }
    /**
     * Test l'action lors on selection une collectivité à migrer,
     * avec un SIREN qui apartienne à une collectivité qui a été provisioné par ozwillo
     * sur sesile. MAIS que ils ont le MEME collecitvity ID!!
     * Sesile doit que initier la migration sans autres actions
     *
     */
    public function testMigrateCollectivityEqualsAlreadyProvisionedCollectivity()
    {
        $provisionedCollectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $this->client->enableProfiler();
        $siren = $provisionedCollectivity->getSiren();
        $postData = [
            'orgId' => $provisionedCollectivity->getId(),
            'siren' => $siren,
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(201, $this->client);
        /**
         * check DB
         */
        $this->em->clear();
        $testCollectivity = $this->em->getRepository(Collectivite::class)->find($provisionedCollectivity->getId());
        self::assertEquals($siren, $testCollectivity->getSiren());
        self::assertInstanceOf(CollectiviteOzwillo::class, $testCollectivity->getOzwillo());

        $testSesileMigration = $this->em->getRepository(SesileMigration::class)->findOneBy(
            ['collectivityId' => $provisionedCollectivity->getId()]
        );
        self::assertEquals($siren, $testSesileMigration->getSiren());
        self::assertEquals(SesileMigration::STATUS_EN_COURS, $testSesileMigration->getStatus());
        self::assertEquals($provisionedCollectivity->getNom(), $testSesileMigration->getCollectivityName());
        self::assertFalse($testSesileMigration->hasUsersExported());
    }

    public function testMigrateCollectivityReturn400WhenNoSirenIsSet()
    {
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $postData = [
            'siren' => '1212',
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(400, $this->client);
    }

    public function testMigrateCollectivityReturn400WhenNoOrgIdIsSet()
    {
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $postData = [
            'orgId' => '1212',
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(400, $this->client);
    }

    public function testMigrateCollectivityReturnForbidden302WhenNoAuthenticatedAsSuperAdmin()
    {
        $user = $this->fixtures->getReference(UserFixtures::USER_ONE_REFERENCE);
        $this->logIn($user);
        $postData = [
            'orgId' => '1212',
        ];
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/org/migrate/init'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(403, $this->client);
    }

    public function testListMigrationAction()
    {
        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $this->client->request('GET', '/api/migration/v3v4/dashboard');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $content);
    }

    public function testOzwilloUserExportAction()
    {
        $provisionedCollectivity = $this->fixtures->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE);

        $sesileUserMigrator = $this->getSesileUserMigratorMock();
        $sesileUserMigrator->expects(self::once())
            ->method('exportCollectivityUsers')
            ->willReturn(
                new Message(
                    true,
                    new MigrationReport(
                        [['id' => 1], ['id' => 2], ['id' => 3]],
                        $provisionedCollectivity->getOzwillo(),
                        '123454-54464-5454'
                    )
                )
            );
        //override service inside the container with the mock object
        $this->client->getContainer()->set('sesile_user.migrator', $sesileUserMigrator);

        $superUser = $this->fixtures->getReference(UserFixtures::USER_SUPER_REFERENCE);
        $this->logIn($superUser);
        $postData = [
            'orgId' => $provisionedCollectivity->getId(),
        ];
        $this->client->enableProfiler();
        $this->client->request(
            'POST',
            sprintf('/api/migration/v3v4/ozwillo/users'),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        $this->assertStatusCode(200, $this->client);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(3, $responseContent['countUsers']);
        self::assertArrayHasKey('organizationId', $responseContent);
        self::assertArrayHasKey('instanceId', $responseContent);
        self::assertArrayHasKey('creatorId', $responseContent);
        self::assertArrayHasKey('serviceId', $responseContent);

        /**
         * check email was sent
         */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        self::assertSame(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        self::assertEquals(
            $this->getContainer()->getParameter('email_sender_address'),
            key($collectedMessages[0]->getFrom())
        );
        self::assertEquals($this->getContainer()->getParameter('contact'), key($collectedMessages[0]->getTo()));
        $assertBodyContent = sprintf(
            'La migration de la collectivité "%s" à bien été effectué avec le code SIREN %s.',
            $provisionedCollectivity->getNom(),
            $provisionedCollectivity->getSiren()
        );
        self::assertContains($assertBodyContent, $collectedMessages[0]->getBody());
        /**
         * check DB
         */
        $this->em->clear();
        $migration = $this->em->getRepository(SesileMigration::class)->findOneBy(
            ['collectivityId' => $provisionedCollectivity->getId()]
        );
        self::assertTrue($migration->hasUsersExported());
        self::assertEquals(SesileMigration::STATUS_FINALISE, $migration->getStatus());
        //assert list of sesile migration history after user export action.
        $this->client->request('GET', '/api/migration/v3v4/dashboard');
        $this->assertStatusCode(200, $this->client);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $content);
        $collectivitiesIdsInMigration = array_map(
            function ($migration) {
                return $migration['collectivityId'];
            },
            $content
        );
        //find the one we need to assert
        $assertKey = array_flip($collectivitiesIdsInMigration)[(int)$provisionedCollectivity->getId()];
        $migration = $content[$assertKey];
        self::assertTrue(in_array($provisionedCollectivity->getId(), $collectivitiesIdsInMigration));
        self::assertEquals(0, $migration['allowExport']);

    }

    private function getSesileUserMigratorMock()
    {
        /**
         * mock OzwilloUserMigrator
         */
        $sesileUserMigrator = $this->getMockBuilder(OzwilloUserMigrator::class)
            ->disableOriginalConstructor()
            ->setMethods(['exportCollectivityUsers'])->getMock();

        return $sesileUserMigrator;
    }

    private function persistCollectivity($domain = 'domain', $name = 'org Name', $withOzwillo = false)
    {
        $aValidCollectivity = CollectiviteFixtures::aValidCollectivite($domain, $name, null);
        if (true === $withOzwillo) {
            $collectiviteOzwillo = CollectiviteFixtures::aValidCollectiviteOzwillo($aValidCollectivity);

            $this->em->persist($collectiviteOzwillo);
        }
        $this->em->persist($aValidCollectivity);
        $this->em->flush();

        return $aValidCollectivity;
    }

}