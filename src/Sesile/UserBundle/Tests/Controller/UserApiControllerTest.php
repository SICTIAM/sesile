<?php

namespace Sesile\UserBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

/**
 * Class UserApiControllerTest
 * @package Sesile\UserBundle\Tests\Controller
 */
class UserApiControllerTest extends SesileWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testPostUserAction()
    {
        $collectivite = $this->fixtures->getReference('collectivite-one');

        $postData = [
            'email' => 'email@test.com',
            'username' => 'usernameTest'
        ];
        $this->client->request(
            'POST',
            sprintf('/apirest/user/new/%s', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );

        self::assertEquals(202, $this->client->getResponse()->getStatusCode());
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $data = $entityManager->getRepository(User::class)->findOneBy(['email' => 'email@test.com']);
        self::assertInstanceOf(User::class, $data);
        self::assertCount(1, $data->getCollectivities());
        self::assertInstanceOf(Collectivite::class, $data->getCollectivities()->first());
        self::assertEquals($collectivite->getId(), $data->getCollectivities()->first()->getId());
    }

    public function testGetCurrentAction()
    {
        $currentCollectivityId = $this->fixtures->getReference('collectivite-one')->getId();
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', '/apirest/user/current');
        $this->assertStatusCode(200, $this->client);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($user->getId(), $data['id']);
        self::assertEquals($currentCollectivityId, $data['current_org_id']);
    }

    public function testUsersCollectiviteAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $currentCollectivityId = $this->fixtures->getReference('collectivite-one')->getId();
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', sprintf('/apirest/users/%s', $currentCollectivityId));
        $this->assertStatusCode(200, $this->client);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $data);
        self::assertEquals($user->getId(), $data[0]['id']);
    }

    public function testUsersCollectiviteSelectAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $currentCollectivityId = $this->fixtures->getReference('collectivite-one')->getId();
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', sprintf('/apirest/users-select/%s', $currentCollectivityId));
        $this->assertStatusCode(200, $this->client);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $data);
        self::assertArraySubset(['label' => $user->getPrenom() . " " . $user->getNom(), 'value' => $user->getId()], $data[0]);
    }

    public function testFindByNomOrPrenomAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $currentCollectivityId = $this->fixtures->getReference('collectivite-one')->getId();
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', sprintf('/apirest/user/search?value=%s&collectiviteId=%s', $user->getNom(), $currentCollectivityId));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($user->getId(), $data[0]['id']);
        //try with prenom
        $this->client->request('GET', sprintf('/apirest/user/search?value=%s&collectiviteId=%s', $user->getPrenom(), $currentCollectivityId));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($user->getId(), $data[0]['id']);
    }

    public function testFindByNomOrPrenomActionShouldReturn404IfUserHasNotTheCollectivity()
    {
        $user = $this->fixtures->getReference('user-one');
        $currentCollectivityId = $this->fixtures->getReference('collectivite-two')->getId();
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', sprintf('/apirest/user/search?value=%s&collectiviteId=%s', $user->getNom(), $currentCollectivityId));
        $this->assertStatusCode(404, $this->client);
    }

    public function testFindByNomOrPrenomActionShouldReturnDataIfSuperAdminCalls()
    {
        $user = $this->fixtures->getReference('user-super');
        $currentCollectivityId = $this->fixtures->getReference('collectivite-two')->getId();
        $this->logIn($user, $currentCollectivityId);
        $this->client->request('GET', sprintf('/apirest/user/search?value=%s&collectiviteId=%s', $user->getNom(), $currentCollectivityId));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(0, $data);
    }

}
