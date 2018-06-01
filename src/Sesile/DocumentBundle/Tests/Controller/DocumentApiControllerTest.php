<?php


namespace Sesile\DocumentBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\DocumentFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentApiControllerTest extends SesileWebTestCase
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

    public function testUploadDocumentAction()
    {

        $fs = new Filesystem();
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference(DocumentFixtures::DOCUMENT_REFERENCE_XML);
        //copy file to upload/fisc path
        $fixtureFilePath =  $this->getContainer()->get('kernel')->getRootDir().'/../src/Sesile/MainBundle/DataFixtures/helios.xml';
        $filePath = $this->getContainer()->getParameter('upload')['fics'];
        $fs->copy($fixtureFilePath, $filePath.'helios.xml', true);
        $fs->copy($fixtureFilePath, __DIR__.'/../helios.xml', true);
        $this->logIn($user);
        $file = new UploadedFile(
            __DIR__.'/../helios.xml',
            'helios.xml',
            'application/xml',
            null,
            null
        );
        $this->client->request(
            'POST',
            sprintf(
                '/apirest/document/uploaddocument/%s/%s',
                $document->getId(),
                $document->getToken()
            ),
            array(),
            ['upload-file' => $file]
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals('ok', $response['error']);
        self::assertArrayHasKey('url', $response);
        self::assertFileExists($filePath.'helios.xml');
        $fs->remove($filePath.'helios.xml');
    }

    public function testUploadDocumentActionMustFailIfWrongTokenGiven()
    {

        $fs = new Filesystem();
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference(DocumentFixtures::DOCUMENT_REFERENCE_XML);
        //copy file to upload/fisc path
        $fixtureFilePath =  $this->getContainer()->get('kernel')->getRootDir().'/../src/Sesile/MainBundle/DataFixtures/helios.xml';
        $filePath = $this->getContainer()->getParameter('upload')['fics'];
        $fs->copy($fixtureFilePath, $filePath.'helios.xml', true);
        $fs->copy($fixtureFilePath, __DIR__.'/../helios.xml', true);
        $this->logIn($user);
        $file = new UploadedFile(
            __DIR__.'/../helios.xml',
            'helios.xml',
            'application/xml',
            null,
            null
        );
        $this->client->request(
            'POST',
            sprintf(
                '/apirest/document/uploaddocument/%s/%s',
                $document->getId(),
                'wrongToken'
            ),
            array(),
            ['upload-file' => $file]
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals('nodocumentwiththisname', $response['error']);
    }

}