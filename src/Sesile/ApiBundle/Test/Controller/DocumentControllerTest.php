<?php


namespace Sesile\ApiBundle\Test\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\DocumentBundle\Entity\Document;
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

class DocumentControllerTest extends SesileWebTestCase
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
                ClasseurFixtures::class,
                DocumentFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testGetAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference('document-one');
        $this->client->request(
            'GET',
            sprintf('/api/document/%s', $document->getId()),
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
        self::assertEquals($document->getId(), $responseData['id']);
    }

    public function testUpdateAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference('document-one');
        $filePath = $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/docs/testFile.txt';
        $fs = new Filesystem();
        $fs->dumpFile($filePath, 'test content');
        $file = new UploadedFile(
            $filePath,
            'testFile.txt',
            'text/plain',
            123
        );
        $this->client->request(
            'POST',
            sprintf('/api/document/%s/edit?signed=1', $document->getId()),
            array(),
            ['file' => $file],
            array(
//                'CONTENT_TYPE' => 'application/json',
                'HTTP_token' => $user->getApitoken(),
                'HTTP_secret' => $user->getApisecret()
            ),
            ['signed' => 1]
        );
        $this->assertStatusCode(200, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($document->getId(), $responseData['id']);
        $filePath = $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/docs/'.$responseData['repourl'];
        self::assertFileExists($filePath);
        $fs->remove($filePath);
    }

    public function testDeleteAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference('document-one');
        $this->client->request(
            'DELETE',
            sprintf('/api/document/%s', $document->getId()),
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
        self::assertArraySubset(['code' => 200, 'message' => 'Document supprimé'], $responseData);

        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $newDocument = $entityManager->getRepository(Document::class)->find($document->getId());
        self::assertNull($newDocument);
    }

    public function testDownloadAction()
    {
        $user = $this->fixtures->getReference('user-one');
        $document = $this->fixtures->getReference('document-one');
        $filePath = $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/docs/testFile.txt';
        $fs = new Filesystem();
        $fs->dumpFile($filePath, 'test content');
        $this->client->request(
            'GET',
            sprintf('/api/document/%s/content', $document->getId()),
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
        self::assertEquals($document->getId(), $responseData['id']);
    }

}