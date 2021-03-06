<?php

namespace Sesile\ClasseurBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\DocumentFixtures;
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
                DocumentFixtures::class
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

        $this->client->enableProfiler();
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
         * check email was sent
         */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        self::assertSame(2, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        self::assertEquals($this->fixtures->getReference('user-one')->getEmail(), key($collectedMessages[0]->getTo()));
        self::assertEquals($this->fixtures->getReference('user-one')->getEmail(), key($collectedMessages[1]->getTo()));
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $data = $entityManager->getRepository(Classeur::class)->findOneBy(['nom' => 'The Name']);
        self::assertInstanceOf(Classeur::class, $data);
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
        self::assertCount(1, $data['list']);
        self::assertEquals($classeur->getId(), $data['list'][0]['id']);
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
        self::assertCount(0, $data['list']);
    }

    public function testValidActionShouldReturnAllClasseursToBeValidatedByTheUser()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/valid/%s/%s/%s/%s/%s',
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
        self::assertCount(1, $data['list']);
        self::assertEquals($classeur->getId(), $data['list'][0]['id']);
    }

    public function testListRetractActionShouldReturnAllClasseursThatCanBeRetractableByTheUser()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/retract/%s/%s/%s/%s/%s',
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
        self::assertCount(1, $data['list']);
        self::assertEquals($classeur->getId(), $data['list'][0]['id']);
    }

    public function testListRemovableActionShouldReturnAllClasseursThatCanBeRemovedByTheUser()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        //edit status (mock the action)
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
        $classeur = $em->getRepository(Classeur::class)->find($classeur->getId());
        $classeur->setStatus(3);
        $em->flush();
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/remove/%s/%s/%s/%s/%s',
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
        self::assertCount(1, $data['list']);
        self::assertEquals($classeur->getId(), $data['list'][0]['id']);
    }

    public function testGetClasseurByIdShouldReturnSerializedClasseur()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/%s',
                $collectivite->getId(),
                $classeur->getId()
            )
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($classeur->getId(), $data['id']);
        self::assertEquals($collectivite->getId(), $data['collectivite']['id']);
    }

    public function testGetClasseurByIdShouldReturn404WhenNotFound()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/org/%s/classeurs/%s',
                $collectivite->getId(),
                $classeur->getId()
            )
        );
        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchClasseursAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $postData = [
            'name' => 'Classeur'
        ];
        $this->client->request(
            'POST',
            sprintf('/api/v4/org/%s/classeurs/search', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data);

        self::assertEquals($classeur->getId(), $data[0]['id']);
        self::assertEquals($classeur->getDescription(), $data[0]['description']);
        self::assertEquals($classeur->getNom(), $data[0]['nom']);
    }
    public function testSearchClasseursActionShouldReturnEmptyIfNothingFound()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $postData = [
            'name' => 'Nothing to find'
        ];
        $this->client->request(
            'POST',
            sprintf('/api/v4/org/%s/classeurs/search', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(0, $data);
    }
    public function testSearchClasseursActionShouldReturnBadRequestIfNoSearchTermIsGiven()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $postData = [
        ];
        $this->client->request(
            'POST',
            sprintf('/api/v4/org/%s/classeurs/search', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
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

    public function testJnlpSignerFilesAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->logIn($user);
        $classeur = $this->fixtures->getReference('classeur-one');
        $collectivite = $this->fixtures->getReference('collectivite-two');
        $this->client->request(
            'GET',
            sprintf(
                '/api/v4/jnlpsignerfiles/%s/%s',
                $classeur->getId(),
                $user->getUserrole()->first()->getId()
            )
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    private function getValidJnlp($ids, $roleId, $classeur)
    {
        return sprintf('<?xml version="1.0" encoding="utf-8"?>
                <jnlp spec="1.0+" codebase="http://sesile-dev.local/api/v4/jnlpsignerfiles/%s/%s">
                  <information>
                    <title>SESILE JWS Signer</title>
                    <vendor>SICTIAM</vendor>
                    <homepage href="http://signature.dev.sesile.fr/jws/sesile-jws-signer.jar"/>
                    <description>Application de de signature de documents</description>
                    <description kind="short">Application de signatures</description>
                    <offline-allowed/>
                  </information>
                <security><all-permissions /></security>
                  <resources>
                    <j2se version="1.8" initial-heap-size="128m" max-heap-size="1024m"/>
                    <jar href="http://signature.dev.sesile.fr/jws/sesile-jws-signer.jar"/>
                  </resources>
                  <application-desc ><argument>[{"name":"test refactor 1","url_valid_classeur":"http:\/\/sesile-dev.local\/api\/v4\/valider_classeur_jws\/632\/75","documents":[{"name":"test.xml","type":"cades","description":"desc2","url_file":"http:\/\/sesile-dev.local\/apirest\/document\/downloadJWS\/fad9e2e3820af602136e7bf896f8c434ca85baaf.","url_upload":"http:\/\/sesile-dev.local\/apirest\/document\/uploaddocument\/591"}]}]</argument><argument>Non renseigné</argument><argument>Non renseignée</argument><argument>Non renseigné</argument><argument>padawan</argument><argument>5b1106bceaab8</argument></application-desc>
        </jnlp>',
        urlencode(serialize($ids)),
        $roleId
        );
    }

}
