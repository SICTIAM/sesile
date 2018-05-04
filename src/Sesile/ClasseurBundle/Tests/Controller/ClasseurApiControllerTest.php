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
use Sesile\UserBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;

class ClasseurApiControllerTest extends WebTestCase
{
    private $client = null;
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
        $this->client = static::createClient();
    }

    public function testList()
    {
        $this->logIn();
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

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'secured_area';
        $ozwilloAccessToken = [
            "access_token" => "eyJpZCI6ImQ2NGNlMzE4LTU0NjYtNDIwYi1hZDFjLTkzZTU3OGE1NTQ5MS9Tc0ktYlhNOG1RS3RaQnBwdXdzM0JRIiwiaWF0IjoxNTI1MzYzODMwLjk2NDAwMDAwMCwiZXhwIjoxNTI1MzY3NDMwLjk2NDAwMDAw",
            "token_type" => "Bearer",
            "expires_in" => 3600,
            "scope" => "openid profile email",
            "id_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02M",
        ];

        $token = new OAuthToken($ozwilloAccessToken, array('ROLE_ADMIN'));
        $token->setUser($this->fixtures->getReference('user-one'));

        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

}
