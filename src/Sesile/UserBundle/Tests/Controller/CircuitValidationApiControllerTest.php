<?php


namespace Sesile\UserBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class CircuitValidationApiControllerTest extends SesileWebTestCase
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
                CircuitValidationFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testListByCollectiviteAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $circuitValidation = $this->fixtures->getReference('circuit-validation');
        $this->client->request('GET', sprintf('/apirest/circuit_validations/%s', $collectivite->getId()));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($circuitValidation->getId(), $data[0]['id']);
    }

    public function testListByCollectiviteActionShouldReturnForSuperAdmin()
    {
        $this->logIn($this->fixtures->getReference('user-super'));
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request('GET', sprintf('/apirest/circuit_validations/%s', $collectivite->getId()));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        $circuitValidation = $this->fixtures->getReference('circuit-validation-two');
        self::assertEquals($circuitValidation->getId(), $data[0]['id']);
    }

    public function testListByCollectiviteActionShouldReturn404IfUserNotAuthorizedOnTheCollectivity()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectiviteTwo = $this->fixtures->getReference('collectivite-two');
        $this->client->request('GET', sprintf('/apirest/circuit_validations/%s', $collectiviteTwo->getId()));
        $this->assertStatusCode(404, $this->client);
    }

    public function testListByUserAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $circuitValidation = $this->fixtures->getReference('circuit-validation');
        $this->client->request('GET', sprintf('/apirest/circuit_validations_user/orgId/%s', $collectivite->getId()));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($circuitValidation->getId(), $data[0]['id']);
    }
}