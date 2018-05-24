<?php


namespace Sesile\ApiBundle\Test\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class UserControllerTest extends SesileWebTestCase
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
                UserFixtures::class,
                TypeClasseurFixtures::class,
                UserPackFixtures::class,
                CircuitValidationFixtures::class,
                ClasseurFixtures::class,
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testGetUserAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($user->getId(), $responseData['id']);
        self::assertEquals($user->getUsername(), $responseData['username']);
        self::assertEquals($user->getEmail(), $responseData['email']);
        self::assertEquals($user->getPrenom(), $responseData['prenom']);
        self::assertEquals($user->getNom(), $responseData['nom']);
    }

    public function testIndexAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/all'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(3, $responseData);
        self::assertEquals($user->getId(), $responseData[0]['id']);
        self::assertEquals('username2', $responseData[1]['username']);
        self::assertEquals('super', $responseData[2]['username']);
    }

    public function testGetServicesOrganisationnelsAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $type = $this->fixtures->getReference('classeur-type-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/services/types/%s', $type->getId()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $responseData);
        self::assertEquals($this->fixtures->getReference('circuit-validation')->getId(), $responseData[0]['id']);
        self::assertEquals($this->fixtures->getReference('circuit-validation')->getNom(), $responseData[0]['nom']);
        self::assertEquals($this->fixtures->getReference('circuit-validation-two')->getId(), $responseData[1]['id']);
        self::assertEquals($this->fixtures->getReference('circuit-validation-two')->getNom(), $responseData[1]['nom']);
    }

    public function testGetServicesOrganisationnelsForUserAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $type = $this->fixtures->getReference('classeur-type-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/services/%s', $user->getEmail()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $responseData);
        self::assertEquals($this->fixtures->getReference('circuit-validation')->getId(), $responseData[0]['id']);
        self::assertEquals($this->fixtures->getReference('circuit-validation')->getNom(), $responseData[0]['nom']);
        self::assertEquals($this->fixtures->getReference('circuit-validation')->getTypes()->first()->getId(), $responseData[0]['type_classeur'][0]);
    }

}