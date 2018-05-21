<?php


namespace Sesile\ApiBundle\Test\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class ClasseurControllerTest extends SesileWebTestCase
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
                ClasseurFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testIndexAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            '/api/classeur/',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
    }

    public function testGetAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $classeur = $this->fixtures->getReference('classeur-one');
        $this->client->request(
            'GET',
            sprintf('/api/classeur/%s', $classeur->getId()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
    }

    public function testNewAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $typeClasseur = $this->fixtures->getReference('classeur-type-one');
        $circuitDeValidation = $this->fixtures->getReference('circuit-validation');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $data = [
            'name' => 'name',
            'desc' => 'description',
            'validation' => '11/08/2018',
            'type' => $typeClasseur->getId(),
            'groupe' => $circuitDeValidation->getId(),
            'visibilite' => 0,
            'siren' => $collectivite->getSiren()
//            'email' => $user->getEmail()
        ];
        $this->client->request(
            'POST',
            sprintf('/api/classeur/'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            ),
            json_encode($data)
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $classeur = $entityManager->getRepository(Classeur::class)->find($responseData['id']);
        self::assertInstanceOf(Classeur::class, $classeur);
        self::assertEquals($collectivite->getId(), $classeur->getCollectivite()->getId());
        self::assertEquals($user->getId(), $classeur->getUser()->getId());
    }

    public function testNewActionShouldSucceedIfNoSirenIsGiven()
    {
        $user = $this->fixtures->getReference('user-one');
        $typeClasseur = $this->fixtures->getReference('classeur-type-one');
        $circuitDeValidation = $this->fixtures->getReference('circuit-validation');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $data = [
            'name' => 'name',
            'desc' => 'description',
            'validation' => '11/08/2018',
            'type' => $typeClasseur->getId(),
            'groupe' => $circuitDeValidation->getId(),
            'visibilite' => 0,
        ];
        $this->client->request(
            'POST',
            sprintf('/api/classeur/'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            ),
            json_encode($data)
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $classeur = $entityManager->getRepository(Classeur::class)->find($responseData['id']);
        self::assertInstanceOf(Classeur::class, $classeur);
        self::assertEquals($collectivite->getId(), $classeur->getCollectivite()->getId());
        self::assertEquals($user->getId(), $classeur->getUser()->getId());
    }

}