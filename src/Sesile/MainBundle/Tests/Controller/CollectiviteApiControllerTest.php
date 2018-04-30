<?php

namespace Sesile\MainBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Class CollectiviteApiControllerTest
 * @package Sesile\MainBundle\Tests\Controller
 */
class CollectiviteApiControllerTest extends WebTestCase
{

    public function testGetAllOrganisations()
    {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/apirest/collectivite/list');
        $this->assertStatusCode(200, $client);
    }
}