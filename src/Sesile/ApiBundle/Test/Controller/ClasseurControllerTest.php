<?php


namespace Sesile\ApiBundle\Test\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    }

    public function testDeleteActionShouldSucceed()
    {
        $user = $this->fixtures->getReference('user-one');
        $classeur = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE);
        $circuitDeValidation = $this->fixtures->getReference('circuit-validation');
        $this->client->request(
            'DELETE',
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

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(['code' => '200', 'message' => 'Classeur retiré'], $responseData);
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $data = $entityManager->getRepository(Classeur::class)->find($classeur->getId());
        self::assertInstanceOf(Classeur::class, $data);
        self::assertEquals(3, $data->getStatus());
    }

    public function testNewDocumentActionShouldSucceed()
    {
        //create a file
        $fs = new Filesystem();
        $fs->dumpFile(__DIR__.'/../testFile.txt', 'test content');
        $user = $this->fixtures->getReference('user-one');
        $classeur = $this->fixtures->getReference(ClasseurFixtures::CLASSEURS_REFERENCE);
        $file = new UploadedFile(
            __DIR__.'/../testFile.txt',
            'testFile.txt',
            'text/plain',
            123
        );
        $this->client->request(
            'POST',
            sprintf('/api/classeur/%s/newDocuments', $classeur->getId()),
            array(),
            [$file],
            array(
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $newDocument = $entityManager->getRepository(Document::class)->find($response['id']);
        self::assertInstanceOf(Document::class, $newDocument);
        self::assertEquals($classeur->getId(), $newDocument->getClasseur()->getId());
        $filePath = $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/docs/'.$response['repourl'];
        self::assertFileExists($filePath);
        $fs->remove($filePath);
    }

    public function testGetTypesAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $this->client->request(
            'GET',
            sprintf('/api/classeur/types/'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            )
        );
        $this->assertStatusCode(200, $this->client);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $response);
        self::assertEquals($this->fixtures->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE)->getId(), $response[0]['id']);
        self::assertEquals($this->fixtures->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_TWO_REFERENCE)->getId(), $response[1]['id']);
    }

    public function testNewActionHeliosTypeIdRetroCompatibility()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = $this->fixtures->getReference('user-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');

        $type = TypeClasseurFixtures::aValidClasseurType('Helios', $collectivite);
        $em->persist($type);

        $etape = CircuitValidationFixtures::aValidEtapeGroupe(0, [$user]);
        $em->persist($etape);

        $circuitDeValidation = CircuitValidationFixtures::aValidCircuitDeValidation(
            'circuit helios',
            $collectivite,
            [$type],
            [$etape]
        );
        $em->persist($circuitDeValidation);


        $em->flush();
        $data = [
            'name' => 'name',
            'desc' => 'description',
            'validation' => '11/08/2018',
            'type' => 2,
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

}
