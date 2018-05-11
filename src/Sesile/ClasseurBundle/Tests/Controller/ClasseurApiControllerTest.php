<?php

namespace Sesile\ClasseurBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
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
                ClasseurFixtures::class,
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testPostAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));

        $collectivite = $this->fixtures->getReference('collectivite-one');
        $formData = $this->getFormData();
        $postData = [
            'circuit_id' => $formData['circuitValidation']->getId(),
            'copy' => [$formData['user']->getId()],
            'description' => "test",
            'etapeClasseurs' => [
                [
                    "ordre" => "0",
                    "users" => [$formData['user']->getId()],
                    "user_packs" => [$formData['userPack']->getId()],
                ],
                ["ordre" => "1", "users" => [$formData['user']->getId()]],
            ],
            'nom' => "The Name",
            'type' => $formData['typeClasseur']->getId(),
            'user' => $formData['user']->getId(),
            'validation' => "2018-05-03 11:36",
            'visibilite' => 0,
            'collectivite' => $formData['collectivite']->getId(),
        ];

        $this->client->request(
            'POST',
            sprintf('/api/v4/classeur/new', $collectivite->getId()),
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
        $data = $entityManager->getRepository(Classeur::class)->findOneBy(['nom' => 'The Name']);
        self::assertInstanceOf(Classeur::class, $data);
//        var_dump($data->getVisible());exit;
        self::assertEquals('The Name', $data->getNom());
        self::assertEquals("test", $data->getDescription());
        self::assertEquals($formData['circuitValidation']->getId(), $data->getCircuitId()->getId());
        self::assertEquals($formData['user']->getId(), $data->getCopy()->first()->getId());
        //typeClasseur
        self::assertEquals($formData['typeClasseur']->getId(), $data->getType()->getId());
        //EtapeClasseur
        self::assertCount(2, $data->getEtapeClasseurs());
        self::assertEquals(0, $data->getEtapeClasseurs()->first()->getOrdre());
        self::assertEquals(1, $data->getEtapeClasseurs()->last()->getOrdre());
        self::assertEquals(
            $formData['user']->getId(),
            $data->getEtapeClasseurs()->first()->getUsers()->first()->getId()
        );

        self::assertEquals($formData['user']->getId(), $data->getUser()->getId());
        self::assertEquals("2018-05-03 11:36", $data->getValidation()->format('Y-m-d H:i'));
        self::assertEquals(0, $data->getVisibilite());
        self::assertEquals($formData['collectivite']->getId(), $data->getCollectivite()->getId());
    }

    public function testUpdateActionShouldReturnSuccess()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $formData = $this->getFormData();
        $postData = [
            'description' => "New Descirption",
            'etapeClasseurs' => [
                [
                    "ordre" => "0",
                    "users" => [$formData['user']->getId()],
                    "user_packs" => [$formData['userPack']->getId()],
                ],
                ["ordre" => "1", "users" => [$formData['user']->getId()]],
                ["ordre" => "2", "users" => [$formData['user']->getId()]],
            ],
            'nom' => "New Name",
        ];

        $this->client->request(
            'PATCH',
            '/api/v4/classeur/'.$classeur->getId(),
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
        $data = $entityManager->getRepository(Classeur::class)->findOneBy(['nom' => 'New Name']);
        self::assertInstanceOf(Classeur::class, $data);
        self::assertEquals('New Name', $data->getNom());
        self::assertEquals("New Descirption", $data->getDescription());
        self::assertEquals($formData['circuitValidation']->getId(), $data->getCircuitId()->getId());
        self::assertEquals($formData['user2']->getId(), $data->getCopy()->first()->getId());
        self::assertEquals($formData['typeClasseur']->getId(), $data->getType()->getId());
        //EtapeClasseur
        self::assertCount(3, $data->getEtapeClasseurs());
        self::assertEquals(0, $data->getEtapeClasseurs()->first()->getOrdre());
        self::assertEquals(2, $data->getEtapeClasseurs()->last()->getOrdre());
        self::assertEquals(
            $formData['user']->getId(),
            $data->getEtapeClasseurs()->first()->getUsers()->first()->getId()
        );


        self::assertEquals($formData['user']->getId(), $data->getUser()->getId());
        self::assertEquals("2018-06-03 11:36", $data->getValidation()->format('Y-m-d H:i'));
        self::assertEquals(1, $data->getVisibilite());
        self::assertEquals($formData['collectivite']->getId(), $data->getCollectivite()->getId());
    }

    public function testUpdateActionShouldReturn400WhenWrongParametersAreSent()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $formData = $this->getFormData();
        $postData = [
            'circuit_id' => $formData['circuitValidation']->getId(),
            'copy' => [$formData['user']->getId()],
            'description' => "New Descirption",
            'etapeClasseurs' => [
                [
                    "ordre" => "0",
                    "users" => [$formData['user']->getId()],
                    "user_packs" => [$formData['userPack']->getId()],
                ],
                ["ordre" => "1", "users" => [$formData['user']->getId()]],
                ["ordre" => "2", "users" => [$formData['user']->getId()]],
            ],
            'nom' => "New Name",
            'type' => $formData['typeClasseur']->getId(),
            'user' => $formData['user']->getId(),
            'validation' => "2018-05-03 11:36",
            'visibilite' => 1,
            'collectivite' => $formData['collectivite']->getId(),
        ];

        $this->client->request(
            'PATCH',
            '/api/v4/classeur/'.$classeur->getId(),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateActionShouldReturn403IdUserNotAuthorized()
    {
        $this->logIn($this->fixtures->getReference('user-two'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $formData = $this->getFormData();
        $postData = [
            'description' => "New Descirption",
            'etapeClasseurs' => [
                [
                    "ordre" => "0",
                    "users" => [$formData['user']->getId()],
                    "user_packs" => [$formData['userPack']->getId()],
                ],
                ["ordre" => "1", "users" => [$formData['user']->getId()]],
                ["ordre" => "2", "users" => [$formData['user']->getId()]],
            ],
            'nom' => "New Name",
        ];

        $this->client->request(
            'PATCH',
            '/api/v4/classeur/'.$classeur->getId(),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testListAllAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('/api/v4/org/%s/classeurs/list/all', $collectivite->getId())/*

            array(),
            array(),
            array(
                'HTTP_token' => 'token_09b7cedb5f9a6df29468b9ddf490ed70',
                'HTTP_secret' => 'secret_abf9411ade3787a8e668ac534f97cf1a'
            )*/
        );

        /**
         * Attention le controlleur vie le TokenListener ne fait aucun control de token.
         * pour controller vraiment, faudra faire ajouter dans le header le HTTP_toket et HTTP_secret
         * et dans le controller : implements TokenAuthenticatedController
         */

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);

        self::assertEquals($classeur->getId(), $data[0]['id']);
        self::assertEquals($classeur->getCollectivite()->getId(), $data[0]['collectivite']['id']);
    }

    public function testListAllClasseursShouldFailIfNoCollectiviteIsGiven()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('api/v4/org/%s/classeurs/list/all', null)
        );

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testListAllActionShouldReturn403WhenNotLogged()
    {
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('api/v4/org/111/classeurs/list/all', $collectivite->getId())
        );

        /**
         * Attention le controlleur redirige ver le root url du projet. donc on a un 302.
         * pour controller vraiment, faudra faire ajouter dans le header le HTTP_toket et HTTP_secret
         */
        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testListActionForUserOneShouldReturnOneClasseur()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/list/%s/%s/%s/%s/%s',
                $collectivite->getId(),
                'id',
                'DESC',
                '15',
                '0',
                $user->getId()
            )
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);
        self::assertEquals($classeur->getId(), $data[0]['id']);
    }

    public function testListActionForSuperUserShouldReturnOnlySuperuserClasseursByCollectivity()
    {
        $superUser = $this->fixtures->getReference('user-super');
        $this->logIn($superUser);
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/list/%s/%s/%s/%s/%s',
                $collectivite->getId(),
                'id',
                'DESC',
                '15',
                '0',
                $superUser->getId()
            )
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(0, $data);
    }

    private function getFormData()
    {
        $typeClasseur = $this->fixtures->getReference('classeur-type-one');
        $user = $this->fixtures->getReference('user-one');
        $user2 = $this->fixtures->getReference('user-two');
        $circuitValidation = $this->fixtures->getReference('circuit-validation');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $userPack = $this->fixtures->getReference('user-pack-one');

        return [
            'typeClasseur' => $typeClasseur,
            'user' => $user,
            'user2' => $user2,
            'circuitValidation' => $circuitValidation,
            'collectivite' => $collectivite,
            'userPack' => $userPack,
        ];
    }

}
