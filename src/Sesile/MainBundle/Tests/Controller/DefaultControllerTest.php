<?php

namespace Sesile\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
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

}
