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
                'HTTP_secret' => $user->getApisecret(),
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
                'HTTP_secret' => $user->getApisecret(),
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
        //load custom fixtures
        $fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
                TypeClasseurFixtures::class,
                UserPackFixtures::class,
                CircuitValidationFixtures::class,
                ClasseurFixtures::class,
            ]
        )->getReferenceRepository();
        $user = $fixtures->getReference('user-one');
        $type = $fixtures->getReference('classeur-type-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/services/types/%s', $type->getId()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret(),
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $responseData);
        self::assertEquals($fixtures->getReference('circuit-validation')->getId(), $responseData[0]['id']);
        self::assertEquals($fixtures->getReference('circuit-validation')->getNom(), $responseData[0]['nom']);
        self::assertEquals($fixtures->getReference('circuit-validation-two')->getId(), $responseData[1]['id']);
        self::assertEquals($fixtures->getReference('circuit-validation-two')->getNom(), $responseData[1]['nom']);
    }

    public function testGetServicesOrganisationnelsForUserActionShouldReturn301()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/services/%s', $user->getEmail()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret(),
            )
        );
        $this->assertStatusCode(301, $this->client);
    }

    public function testGetCircuitsShouldReturn404IfCollectiviteNotFound()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/%s/org/%s/circuits', $user->getEmail(), 'wrongSIREN'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret(),
            )
        );
        $this->assertStatusCode(404, $this->client);
    }

    public function testGetCircuitsShouldReturnAnArray()
    {
        $fixtures = $this->buildFixturesCircuits();
        $types = $fixtures['types'];
        $circuits = $fixtures['circuits'];
        $user = $this->fixtures->getReference('user-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->client->request(
            'GET',
            sprintf('/api/user/%s/org/%s/circuits', $user->getEmail(), $collectivite->getSiren()),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret(),
            )
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $responseData);
        self::assertEquals('circuit1', $responseData[0]['nom']);
        self::assertEquals($circuits['circuit1']->getId(), $responseData[0]['id']);
        self::assertCount(2, $responseData[0]['type_classeur']);
        self::assertTrue(in_array($types['type1']->getId(), $responseData[0]['type_classeur']));
        self::assertTrue(in_array($types['type2']->getId(), $responseData[0]['type_classeur']));

        self::assertEquals('circuit2', $responseData[1]['nom']);
        self::assertEquals($circuits['circuit2']->getId(), $responseData[1]['id']);
        self::assertCount(1, $responseData[1]['type_classeur']);
        self::assertTrue(in_array($types['type3']->getId(), $responseData[1]['type_classeur']));
    }

    private function buildFixturesCircuits()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $collectivite1 = $this->fixtures->getReference('collectivite-one');
        $collectivite2 = $this->fixtures->getReference('collectivite-two');

        $user1 = $this->fixtures->getReference('user-one');
        $user2 = $this->fixtures->getReference('user-two');
        $user3 = $this->fixtures->getReference('user-super');

        $type1 = TypeClasseurFixtures::aValidClasseurType('typeOne', $collectivite1);
        $em->persist($type1);
        $type2 = TypeClasseurFixtures::aValidClasseurType('typeTwo', $collectivite1);
        $em->persist($type2);
        $type3 = TypeClasseurFixtures::aValidClasseurType('typeThree', $collectivite2);
        $em->persist($type3);

        $userPack1 = UserPackFixtures::aValidUserPack('pack1', $collectivite1, [$user1, $user2, $user3]);
        $em->persist($userPack1);
        $userPack2 = UserPackFixtures::aValidUserPack('pack2', $collectivite1, [$user1]);
        $em->persist($userPack2);
        /**
         * Circuit 1
         */

        $etape1 = CircuitValidationFixtures::aValidEtapeGroupe(0, [$user1], [$userPack1]);
        $em->persist($etape1);
        $etape2 = CircuitValidationFixtures::aValidEtapeGroupe(1, [$user1]);
        $em->persist($etape2);
        $etape3 = CircuitValidationFixtures::aValidEtapeGroupe(1, [], [$userPack2]);
        $em->persist($etape3);
        $etape4 = CircuitValidationFixtures::aValidEtapeGroupe(1, [$user2]);
        $em->persist($etape4);

        $circuit1 = CircuitValidationFixtures::aValidCircuitDeValidation(
            'circuit1',
            $collectivite1,
            [$type1, $type2],
            [$etape1, $etape2]
        );
        $circuit2 = CircuitValidationFixtures::aValidCircuitDeValidation(
            'circuit2',
            $collectivite1,
            [$type3],
            [$etape3]
        );
        $circuit3 = CircuitValidationFixtures::aValidCircuitDeValidation(
            'circuit3',
            $collectivite1,
            [$type3],
            [$etape4]
        );
        $circuit4 = CircuitValidationFixtures::aValidCircuitDeValidation(
            'circuit4',
            $collectivite2,
            [$type3],
            [$etape2]
        );
        $em->persist($circuit1);
        $em->persist($circuit2);
        $em->persist($circuit3);
        $em->persist($circuit4);

        $em->flush();

        return [
            'types' => ['type1' => $type1, 'type2' => $type2, 'type3' => $type3],
            'circuits' => ['circuit1' => $circuit1, 'circuit2' => $circuit2, 'circuit3' => $circuit3, 'circuit4' => $circuit4],
        ];
    }

}