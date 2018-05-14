<?php

namespace Sesile\ClasseurBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class TypeClasseurApiControllerTest extends SesileWebTestCase
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
                UserPackFixtures::class,
                TypeClasseurFixtures::class,
                CircuitValidationFixtures::class,
                ClasseurFixtures::class,
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testGetAllSimpleActionShouldReturnListOfClasseurTypes()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/simple/%s', $collectivite->getId())
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetAllSimpleActionShouldReturnListOfClasseurTypesForSuperAdmin()
    {
        $this->logIn($this->fixtures->getReference('user-super'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/simple/%s', $collectivite->getId())
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetAllSimpleActionShouldReturn404IfUserHasNoAccessOnCollectivite()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/simple/%s', $collectivite->getId())
        );
        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetAllActionShouldReturnListOfClasseurTypes()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/%s', $collectivite->getId())
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($this->fixtures->getReference('classeur-type-one')->getId(), $data[0]['id']);
    }

    public function testGetAllActionShouldReturnListOfClasseurTypesForSuperAdmin()
    {
        $this->logIn($this->fixtures->getReference('user-super'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/%s', $collectivite->getId())
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($this->fixtures->getReference('classeur-type-two')->getId(), $data[0]['id']);
    }

    public function testGetAllAllActionShouldReturn404IfUserHasNoAccessOnCollectivite()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf('/apirest/classeur_types/%s', $collectivite->getId())
        );
        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}