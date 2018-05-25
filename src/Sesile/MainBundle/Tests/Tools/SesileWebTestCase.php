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

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Login mock action for user.
     * it simulates a Ozwillo token and sets the authentication cookie
     *
     * @param UserInterface $user
     * @param $currentCollectivityId
     */
    protected function logIn(UserInterface $user, $currentCollectivityId = null)
    {
        $session = $this->client->getContainer()->get('session');
        // the firewall context defaults to the firewall name
        $firewallContext = 'secured_area';
        $ozwilloAccessToken = [
            "access_token" => "eyJpZCI6IjEzYzhjNGM5LWYxMmYtNDE3NS1iN2Q4LWYwOGEyZWUyN2U0NS92dnFkbE52Q2lqemRqNkhCSHRqMDlnIiwiaWF0IjoxNTI2NDYxMzQ5LjA2NzAwMDAwMCwiZXhwIjoxNTI2NDY0OTQ5LjA2NzAwMDAwMH0",
            "token_type" => "Bearer",
            "expires_in" => 3600,
            "scope" => "openid profile email",
            "id_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02MTJiLTRhNmUtYjJjMy1lMWIyMTVmZTY4NWEiLCJhdWQiOiIyZTc3MTc0Ny1mOTA2LTQxMjUtYmE5Ni04MDY1NTNiYzJjZTIiLCJpYXQiOjE1MjY0NjEzNDksImV4cCI6MTUyNjQ2MTk0OSwibm9uY2UiOm51bGwsImF1dGhfdGltZSI6MTUyNjQ2MTE3NSwiYWNyIjoiaHR0cDovL2VpZGFzLmV1cm9wYS5ldS9Mb0EvbG93IiwiYXBwX3VzZXIiOnRydWV9.P-9a79EveKM23elonX-43aDhWfIVfJgzid-SoZb64MADyYp_MhPPH9LhmHT0Cc31AgdbKbfOi4qA_nuPjIeLecbThNnRplLWWECVlXtmO9IS92F9HRjQNAIG2V4MyygYtJMjcfvvbD6neSWOYl-kULBPN9V5K78qLE7rrHLGCq7DzV96ssL95T4j31Qo2I1HcbUOKgZicF3pTeQ9szJGXIPgVDMHK72urzi-CuGyYRtktMCDFBWz2ZezcMyq7WaofDuV6i40al7O_Y1ntNa2FF00FY8CnZYYcjjGcTpxOoj7KNW41GzfWqCl4natltvPOvpyRw3KPh_qQFmAFNgc0g",
        ];

        $token = new OAuthToken($ozwilloAccessToken, array($user->getRoles()[0]));
        if($currentCollectivityId) {
            $user->setCurrentOrgId($currentCollectivityId);
            $token->setAttributes(['orgId' => $currentCollectivityId]);
        }
        $token->setUser($user);

        $tokenStorage = $this->client->getContainer()->get('security.token_storage');
        $tokenStorage->setToken($token);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

}