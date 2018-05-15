<?php


namespace Sesile\UserBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CircuitValidationFixtures;
use Sesile\MainBundle\DataFixtures\ClasseurFixtures;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\TypeClasseurFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\DataFixtures\UserPackFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class EtapeClasseurApiControllerTest extends SesileWebTestCase
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
        $this->addData();
        parent::setUp();
    }

    public function testGetClasseursValidateByTypeAction()
    {
        $this->logIn($this->fixtures->getReference('user-one'));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $circuitValidation = $this->fixtures->getReference('circuit-validation');
        $this->client->request('GET', sprintf('/apirest/etape_classeurs/org/%s/classeur_stats', $collectivite->getId()));
        $this->assertStatusCode(200, $this->client);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArraySubset(['nom', 'total'], $data[0]);
        self::assertArraySubset(['The type', 1], $data[1]);
    }

    private function addData()
    {
        $userOne = $this->fixtures->getReference('user-one');
        $userPackOne = $this->fixtures->getReference('user-pack-one');

        $etapeClasseur = ClasseurFixtures::aValidEtapeClasseur([$userOne], [$userPackOne], 1, $userOne, false, true);
        $classeur = ClasseurFixtures::aValidClasseur(
            'a valid classeur',
            'a valid classeur desc',
            $userOne,
            [$userOne],
            [],
            $this->fixtures->getReference('circuit-validation'),
            $this->fixtures->getReference('collectivite-one'),
            [$etapeClasseur],
            $this->fixtures->getReference('classeur-type-one')
        );
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($etapeClasseur);
        $em->persist($classeur);
        $em->flush();
    }

}