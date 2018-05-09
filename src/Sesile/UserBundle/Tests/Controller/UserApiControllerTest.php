<?php

namespace Sesile\UserBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

/**
 * Class UserApiControllerTest
 * @package Sesile\UserBundle\Tests\Controller
 */
class UserApiControllerTest extends SesileWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testPostUserAction()
    {
        $collectivite = $this->fixtures->getReference('collectivite-one');

        $postData = [
            'email' => 'email@test.com',
            'username' => 'usernameTest'
        ];
        $this->client->request(
            'POST',
            sprintf('/apirest/user/new/%s', $collectivite->getId()),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($postData)
        );

        self::assertEquals(202, $this->client->getResponse()->getStatusCode());
        /**
         * check database data
         */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();

        $data = $entityManager->getRepository(User::class)->findOneBy(['email' => 'email@test.com']);
        self::assertInstanceOf(User::class, $data);
        self::assertCount(1, $data->getCollectivities());
        self::assertInstanceOf(Collectivite::class, $data->getCollectivities()->first());
        self::assertEquals($collectivite->getId(), $data->getCollectivities()->first()->getId());
    }

}
