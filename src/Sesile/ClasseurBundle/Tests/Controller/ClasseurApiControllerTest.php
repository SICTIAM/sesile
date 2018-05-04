<?php

namespace Sesile\ClasseurBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;

class ClasseurApiControllerTest extends SesileWebTestCase
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
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testPostAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $typeClasseur = $this->fixtures->getReference('classeur-type-one');
        $user = $this->fixtures->getReference('user-one');
        $circuitValidation = $this->fixtures->getReference('circuit-validation');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $userPack = $this->fixtures->getReference('user-pack-one');
        $postData = [
            'circuit_id' => $circuitValidation->getId(),
            'copy' => [$user->getId()],
            'description' => "test",
            'etapeClasseurs' => [
                ["ordre" => "0", "users" => [$user->getId()], "user_packs" => [$userPack->getId()]],
                ["ordre" => "1", "users" => [$user->getId()]],
            ],
            'nom' => "test2",
            'type' => $typeClasseur->getId(),
            'user' => $user->getId(),
            'validation' => "2018-05-03 11:36",
            'visibilite' => "0",
            'collectivite' => $collectivite->getId(),
        ];

        $this->client->request(
            'POST',
            '/apirest/classeur/new',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertTrue(
            $this->client->getResponse()->isSuccessful(),
            sprintf('response status is %s', $this->client->getResponse()->getStatusCode())
        );
        self::assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            ),
            'the "Content-Type" header is "application/json"' // optional message shown on failure
        );
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $data = $entityManager->getRepository(Classeur::class)->findOneBy(['nom' => 'test2']);
        self::assertInstanceOf(Classeur::class, $data);
        self::assertEquals('test2', $data->getNom());
        self::assertEquals("test", $data->getDescription());
        self::assertEquals($circuitValidation->getId(), $data->getCircuitId()->getId());
        self::assertEquals($user->getId(), $data->getCopy()->first()->getId());
        self::assertEquals($typeClasseur->getId(), $data->getType()->getId());
        self::assertEquals($user->getId(), $data->getUser()->getId());
        self::assertEquals("2018-05-03 11:36", $data->getValidation()->format('Y-m-d H:i'));
        self::assertEquals(0, $data->getVisibilite());
        self::assertEquals($collectivite->getId(), $data->getCollectivite()->getId());
    }

}
