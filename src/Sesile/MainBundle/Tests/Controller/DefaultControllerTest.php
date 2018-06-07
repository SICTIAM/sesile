<?php

namespace Sesile\MainBundle\Tests\Controller;

use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class DefaultControllerTest extends SesileWebTestCase
{
    public function testCeckMainDomainShouldReturnTrueWhenCallMadeFromSameHost()
    {
        $client = $this->createClient([], ['HTTP_HOST' => 'sesile.fr']);
        $crawler = $client->request('GET', '/domain/main');
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('main', $content);
        self::assertTrue($content['main']);
        self::assertEquals('sesile.fr', $content['mainDomain']);
        self::assertEquals('sesile.fr', $content['currentDomain']);
    }
    public function testCeckMainDomainShouldReturnFalseWhenCallMadeFromSubdomainHost()
    {
        $client = $this->createClient([], ['HTTP_HOST' => 'subdomain.sesile.fr']);
        $crawler = $client->request('GET', '/domain/main');
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('main', $content);
        self::assertFalse($content['main']);
        self::assertEquals('sesile.fr', $content['mainDomain']);
        self::assertEquals('subdomain.sesile.fr', $content['currentDomain']);
    }

    public function testRedirectionOnSubdomainHost()
    {
        $client = $this->createClient([], ['HTTP_HOST' => $this->client->getContainer()->getParameter('domain')]);
        $crawler = $client->request('GET', '/redirect/testdev1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $url = sprintf('http://testdev1.%s/connect/ozwillo', $client->getContainer()->getParameter('domain'));
        self::assertEquals($url, $client->getResponse()->headers->get('location'));
    }

    public function testRedirectionOnSubdomainHostWhenHttps()
    {
        $client =
            $this->createClient(
                [],
                ['HTTP_HOST' => $this->client->getContainer()->getParameter('domain'), 'HTTPS' => true]);
        $crawler = $client->request('GET', '/redirect/testdev1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $url = sprintf('https://testdev1.%s/connect/ozwillo', $client->getContainer()->getParameter('domain'));
        self::assertEquals($url, $client->getResponse()->headers->get('location'));
    }

}
