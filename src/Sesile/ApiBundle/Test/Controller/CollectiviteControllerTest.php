<?php


namespace Sesile\ApiBundle\Test\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

/**
 * Class CollectiviteControllerTest
 * @package Sesile\ApiBundle\Test\Controller
 */
class CollectiviteControllerTest extends SesileWebTestCase
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
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testPostActionCreateANewCollectivityAndNewAdminUserFromOzwillo()
    {
        $user = $this->fixtures->getReference('user-super');
        $secret = $this->getContainer()->getParameter('ozwillo_secret');
        $data = [
            'instance_id' => 'adb82586-d2f2-4eea-98e9-12999d12c80d',
            'client_id' => '58a71bd9-223f-48e6-a0eb-7aac30355f60',
            'client_secret' => 'secret',
            'user' => [
                'id' => '8dee7298-6a11-431c-861d-4c983fcbd137',
                'name' => 'new admin user',
                'email_address' => "newadmin@test.com"
            ],
            'organization' => [
                'id' => 'd21ad98e-4db7-49e8-a7de-27a2c335b53a',
                'name' => 'organization name',
                'type' => 'PUBLIC_BODY',
                'dc_id' => 'http://data.ozwillo.com/dc/type/orgfr:Organisation_0/FR/987654321'
            ],
            'instance_registration_uri' => 'https://accounts.ozwillo-preprod.eu/acknowledge',
            'authorization_grant' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02MTJiLTRhNmUtYjJjMy1lMWIyMTVmZTY4NWEiLCJhdWQiOiIyZTc3MTc0Ny1mOTA2LTQxMjUtYmE5Ni04MDY1NTNiYzJjZTIiLCJpYXQiOjE1MjY0NjEzNDksImV4cCI6MTUyNjQ2MTk0OSwibm9uY2UiOm51bGwsImF1dGhfdGltZSI6MTUyNjQ2MTE3NSwiYWNyIjoiaHR0cDovL2VpZGFzLmV1cm9wYS5ldS9Mb0EvbG93IiwiYXBwX3VzZXIiOnRydWV9.P-9a79EveKM23elonX-43aDhWfIVfJgzid-SoZb64MADyYp_MhPPH9LhmHT0Cc31AgdbKbfOi4qA_nuPjIeLecbThNnRplLWWECVlXtmO9IS92F9HRjQNAIG2V4MyygYtJMjcfvvbD6neSWOYl-kULBPN9V5K78qLE7rrHLGCq7DzV96ssL95T4j31Qo2I1HcbUOKgZicF3pTeQ9szJGXIPgVDMHK72urzi-CuGyYRtktMCDFBWz2ZezcMyq7WaofDuV6i40al7O_Y1ntNa2FF00FY8CnZYYcjjGcTpxOoj7KNW41GzfWqCl4natltvPOvpyRw3KPh_qQFmAFNgc0g',
                'scope' => 'scope'
            ]

        ];
        $this->client->request(
            'POST',
            sprintf('/api/collectivite/new'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Hub-Signature' => 'sha1='.$this->computeSignature(json_encode($data), $secret)
            ),
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStatusCode(202, $this->client);
        /**
         * check database
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $userData = $em->getRepository(User::class)->findOneBy(['ozwilloId' => '8dee7298-6a11-431c-861d-4c983fcbd137']);
        self::assertInstanceOf(User::class, $userData);
        self::assertCount(1, $userData->getCollectivities());
        self::assertEquals('987654321', $userData->getCollectivities()->first()->getSiren());
        self::assertEquals('organization-name', $userData->getCollectivities()->first()->getDomain());
        self::assertEquals('adb82586-d2f2-4eea-98e9-12999d12c80d', $userData->getCollectivities()->first()->getOzwillo()->getInstanceId());
    }

    public function testPostActionCreateANewCollectivityForExistingAdminUserFromOzwillo()
    {
        $user = $this->fixtures->getReference('user-super');
        $secret = $this->getContainer()->getParameter('ozwillo_secret');
        $data = [
            'instance_id' => 'adb82586-d2f2-4eea-98e9-12999d12c80d',
            'client_id' => '58a71bd9-223f-48e6-a0eb-7aac30355f60',
            'client_secret' => 'secret',
            'user' => [
                'id' => $user->getOzwilloId(),
                'name' => 'new admin user',
                'email_address' => $user->getEmail()
            ],
            'organization' => [
                'id' => 'd21ad98e-4db7-49e8-a7de-27a2c335b53a',
                'name' => 'organization name',
                'type' => 'PUBLIC_BODY',
                'dc_id' => 'http://data.ozwillo.com/dc/type/orgfr:Organisation_0/FR/222222222'
            ],
            'instance_registration_uri' => 'https://accounts.ozwillo-preprod.eu/acknowledge',
            'authorization_grant' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02MTJiLTRhNmUtYjJjMy1lMWIyMTVmZTY4NWEiLCJhdWQiOiIyZTc3MTc0Ny1mOTA2LTQxMjUtYmE5Ni04MDY1NTNiYzJjZTIiLCJpYXQiOjE1MjY0NjEzNDksImV4cCI6MTUyNjQ2MTk0OSwibm9uY2UiOm51bGwsImF1dGhfdGltZSI6MTUyNjQ2MTE3NSwiYWNyIjoiaHR0cDovL2VpZGFzLmV1cm9wYS5ldS9Mb0EvbG93IiwiYXBwX3VzZXIiOnRydWV9.P-9a79EveKM23elonX-43aDhWfIVfJgzid-SoZb64MADyYp_MhPPH9LhmHT0Cc31AgdbKbfOi4qA_nuPjIeLecbThNnRplLWWECVlXtmO9IS92F9HRjQNAIG2V4MyygYtJMjcfvvbD6neSWOYl-kULBPN9V5K78qLE7rrHLGCq7DzV96ssL95T4j31Qo2I1HcbUOKgZicF3pTeQ9szJGXIPgVDMHK72urzi-CuGyYRtktMCDFBWz2ZezcMyq7WaofDuV6i40al7O_Y1ntNa2FF00FY8CnZYYcjjGcTpxOoj7KNW41GzfWqCl4natltvPOvpyRw3KPh_qQFmAFNgc0g',
                'scope' => 'scope'
            ]

        ];
        $this->client->request(
            'POST',
            sprintf('/api/collectivite/new'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Hub-Signature' => 'sha1='.$this->computeSignature(json_encode($data), $secret)
            ),
            json_encode($data)
        );
        $this->assertStatusCode(202, $this->client);
        /**
         * check database
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
        $userData = $em->getRepository(User::class)->find($user->getId());
        self::assertInstanceOf(User::class, $userData);
        self::assertCount(2, $userData->getCollectivities());
        $newCollectivity = $userData->getCollectivities()->last();
        self::assertEquals('222222222', $newCollectivity->getSiren());
        self::assertEquals('adb82586-d2f2-4eea-98e9-12999d12c80d', $newCollectivity->getOzwillo()->getInstanceId());
    }

    public function testPostActionOnExistingCollecitvityWithoutOzwilloMustAddOzwilloData()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $existingCollectivity = CollectiviteFixtures::aValidCollectivite('sictiam', 'Old Sictiam Collectivité', '999999999');
        $em->persist($existingCollectivity);
        $em->flush();
        $em->clear();

        $user = $this->fixtures->getReference('user-super');
        $secret = $this->getContainer()->getParameter('ozwillo_secret');
        $data = [
            'instance_id' => 'adb82586-d2f2-4eea-98e9-12999d12c80d',
            'client_id' => '58a71bd9-223f-48e6-a0eb-7aac30355f60',
            'client_secret' => 'secret',
            'user' => [
                'id' => '8dee7298-6a11-431c-861d-4c983fcbd137',
                'name' => 'new admin user',
                'email_address' => "newadmin@test.com"
            ],
            'organization' => [
                'id' => 'd21ad98e-4db7-49e8-a7de-27a2c335b53a',
                'name' => 'organization name',
                'type' => 'PUBLIC_BODY',
                'dc_id' => 'http://data.ozwillo.com/dc/type/orgfr:Organisation_0/FR/999999999'
            ],
            'instance_registration_uri' => 'https://accounts.ozwillo-preprod.eu/acknowledge',
            'authorization_grant' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02MTJiLTRhNmUtYjJjMy1lMWIyMTVmZTY4NWEiLCJhdWQiOiIyZTc3MTc0Ny1mOTA2LTQxMjUtYmE5Ni04MDY1NTNiYzJjZTIiLCJpYXQiOjE1MjY0NjEzNDksImV4cCI6MTUyNjQ2MTk0OSwibm9uY2UiOm51bGwsImF1dGhfdGltZSI6MTUyNjQ2MTE3NSwiYWNyIjoiaHR0cDovL2VpZGFzLmV1cm9wYS5ldS9Mb0EvbG93IiwiYXBwX3VzZXIiOnRydWV9.P-9a79EveKM23elonX-43aDhWfIVfJgzid-SoZb64MADyYp_MhPPH9LhmHT0Cc31AgdbKbfOi4qA_nuPjIeLecbThNnRplLWWECVlXtmO9IS92F9HRjQNAIG2V4MyygYtJMjcfvvbD6neSWOYl-kULBPN9V5K78qLE7rrHLGCq7DzV96ssL95T4j31Qo2I1HcbUOKgZicF3pTeQ9szJGXIPgVDMHK72urzi-CuGyYRtktMCDFBWz2ZezcMyq7WaofDuV6i40al7O_Y1ntNa2FF00FY8CnZYYcjjGcTpxOoj7KNW41GzfWqCl4natltvPOvpyRw3KPh_qQFmAFNgc0g',
                'scope' => 'scope'
            ]

        ];
        $this->client->request(
            'POST',
            sprintf('/api/collectivite/new'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Hub-Signature' => 'sha1='.$this->computeSignature(json_encode($data), $secret)
            ),
            json_encode($data)
        );
        $this->assertStatusCode(202, $this->client);
        /**
         * check database
         */
        $em->clear();
        $userData = $em->getRepository(User::class)->findOneBy(['ozwilloId' => '8dee7298-6a11-431c-861d-4c983fcbd137']);
        self::assertInstanceOf(User::class, $userData);
        self::assertCount(1, $userData->getCollectivities());
        self::assertEquals('999999999', $userData->getCollectivities()->first()->getSiren());
        self::assertEquals('adb82586-d2f2-4eea-98e9-12999d12c80d', $userData->getCollectivities()->first()->getOzwillo()->getInstanceId());
    }

    public function testPostActionOnExistingCollecitvityAlreadyMapedWithOzwilloMustReturn400()
    {
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $secret = $this->getContainer()->getParameter('ozwillo_secret');
        $data = [
            'instance_id' => 'adb82586-d2f2-4eea-98e9-12999d12c80d',
            'client_id' => '58a71bd9-223f-48e6-a0eb-7aac30355f60',
            'client_secret' => 'secret',
            'user' => [
                'id' => '8dee7298-6a11-431c-861d-4c983fcbd137',
                'name' => 'new admin user',
                'email_address' => "newadmin@test.com"
            ],
            'organization' => [
                'id' => 'd21ad98e-4db7-49e8-a7de-27a2c335b53a',
                'name' => $collectivite->getNom(),
                'type' => 'PUBLIC_BODY',
                'dc_id' => sprintf('http://data.ozwillo.com/dc/type/orgfr:Organisation_0/FR/%s', $collectivite->getSiren())
            ],
            'instance_registration_uri' => 'https://accounts.ozwillo-preprod.eu/acknowledge',
            'authorization_grant' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6Im9hc2lzLm9wZW5pZC1jb25uZWN0LnB1YmxpYy1rZXkifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLm96d2lsbG8tcHJlcHJvZC5ldS8iLCJzdWIiOiJjYmI2N2IxZC02MTJiLTRhNmUtYjJjMy1lMWIyMTVmZTY4NWEiLCJhdWQiOiIyZTc3MTc0Ny1mOTA2LTQxMjUtYmE5Ni04MDY1NTNiYzJjZTIiLCJpYXQiOjE1MjY0NjEzNDksImV4cCI6MTUyNjQ2MTk0OSwibm9uY2UiOm51bGwsImF1dGhfdGltZSI6MTUyNjQ2MTE3NSwiYWNyIjoiaHR0cDovL2VpZGFzLmV1cm9wYS5ldS9Mb0EvbG93IiwiYXBwX3VzZXIiOnRydWV9.P-9a79EveKM23elonX-43aDhWfIVfJgzid-SoZb64MADyYp_MhPPH9LhmHT0Cc31AgdbKbfOi4qA_nuPjIeLecbThNnRplLWWECVlXtmO9IS92F9HRjQNAIG2V4MyygYtJMjcfvvbD6neSWOYl-kULBPN9V5K78qLE7rrHLGCq7DzV96ssL95T4j31Qo2I1HcbUOKgZicF3pTeQ9szJGXIPgVDMHK72urzi-CuGyYRtktMCDFBWz2ZezcMyq7WaofDuV6i40al7O_Y1ntNa2FF00FY8CnZYYcjjGcTpxOoj7KNW41GzfWqCl4natltvPOvpyRw3KPh_qQFmAFNgc0g',
                'scope' => 'scope'
            ]

        ];
        $this->client->request(
            'POST',
            sprintf('/api/collectivite/new'),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Hub-Signature' => 'sha1='.$this->computeSignature(json_encode($data), $secret)
            ),
            json_encode($data)
        );
        $this->assertStatusCode(400, $this->client);
    }

    private function computeSignature($requestBody, $secret)
    {
        return hash_hmac('sha1', $requestBody, $secret);
    }

}