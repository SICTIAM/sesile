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

class DocumentControllerTest extends SesileWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures([

            CollectiviteFixtures::class,
            UserFixtures::class,
            TypeClasseurFixtures::class,
            UserPackFixtures::class,
            CircuitValidationFixtures::class,
            ClasseurFixtures::class,
            DocumentFixtures::class
        ])->getReferenceRepository();
        parent::setUp();
    }

    public function testDownloadVisaAction()
    {
        self::markTestSkipped("Can't manage to make it work for headers sent by SetaPdf");
        $user = $this->fixtures->getReference('user-one');
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $this->logIn($user);
        $document = $this->fixtures->getReference('document-pdf');

        $filePath = sprintf('%s/../src/Sesile/MainBundle/DataFixtures/%s', $this->getContainer()->get('kernel')->getRootDir(), $document->getRepourl());
        $docFilePath = $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/docs/'.$document->getRepourl();
        $fs = new Filesystem();
        $fs->copy($filePath, $docFilePath, true);
        $this->client->request(
            'GET',
            sprintf('/doc/org/%s/download_visa/%s/%s/%s', $collectivite->getId(), $document->getId(), 50, 50)
        );
        //@todo must assert status 200.
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertFileExists($docFilePath);
        $fs->remove($docFilePath);
    }


}