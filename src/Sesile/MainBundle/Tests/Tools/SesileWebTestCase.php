<?php


namespace Sesile\MainBundle\Tests\Tools;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;


class SesileWebTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function logIn(UserInterface $user)
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'secured_area';
        $ozwilloAccessToken = [
            "access_token" => "eyJpZCI6ImQ2NGNlMzE4LTU0NjYtNDIwYi1hZDFjLTkzZTU3OGE1NTQ5MS9Tc0ktYlhNOG1RS3RaQnBwdXdzM0JRIiwiaWF0IjoxNTI1MzYzODMwLjk2NDAwMDAwMCwiZXhwIjoxNTI1MzY3NDMwLjk2NDAwMDAw",
            "token_type" => "Bearer",
            "expires_in" => 3600,
            "scope" => "openid profile email",
            "id_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02M",
        ];

        $token = new OAuthToken($ozwilloAccessToken, array('ROLE_ADMIN'));
        $token->setUser($user);

        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

}