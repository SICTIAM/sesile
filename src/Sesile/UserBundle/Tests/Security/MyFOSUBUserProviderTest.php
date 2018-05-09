<?php


namespace Sesile\UserBundle\Tests\Security;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\OzwilloResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

class MyFOSUBUserProviderTest extends SesileWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EntityManager
     */
    protected $em;

    protected $userProvider;

    public function setUp()
    {
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class,
                UserFixtures::class,
            ]
        )->getReferenceRepository();
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->userProvider = $this->getContainer()->get('my.sesile.user_provider');
        parent::setUp();
    }

    /**
     * Après que l'utilisateur a effectué le login sur ozwillo on ne veut pas qui aie d'autres changement sur la base des donees
     * que l'update de son nom, prenom et l'identifiant ozwillo
     *
     * quand l'utilisateur existe pour la collectivité
     * la collectivité est determiné par le client_id de la collectivité
     */
    public function testLoadUserByOAuthUserResponseShouldUpdateExistingUser()
    {
        $dbUser = $this->em->getRepository(User::class)->find($this->fixtures->getReference('user-one')->getId());
        self::assertNull($dbUser->getOzwilloId());

        $email = $this->fixtures->getReference('user-one')->getEmail();
        $username = $this->fixtures->getReference('user-one')->getUsername();
        $familyName = $this->fixtures->getReference('user-one')->getNom();
        $givenName = $this->fixtures->getReference('user-one')->getPrenom();
        $authData = $this->getAuthUserData($email, $familyName, $givenName);

        /**
         * get \HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse mock
         */
        $userProviderResponseMock = $this->createUserResponseMock();

        $userProviderResponseMock
            ->expects($this->never())
            ->method('getResourceOwner');
//            ->will($this->returnValue($this->createResourceOwnerMock()));

        $userProviderResponseMock
            ->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $userProviderResponseMock
            ->expects($this->never())
            ->method('getRealName')
            ->will($this->returnValue($username));
        $userProviderResponseMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($authData));

        $result = $this->userProvider->loadUserByOAuthUserResponse($userProviderResponseMock);
        self::assertInstanceOf(User::class, $result);

        /**
         * check database
         */
        $this->em->clear();
        $dbUser = $this->em->getRepository(User::class)->find($this->fixtures->getReference('user-one')->getId());
        self::assertEquals('cbb67b1d-612b-4a6e-b2c3-e1b215fe685a', $dbUser->getOzwilloId());
    }

    public function testLoadUserByOAuthUserResponseShouldCreateNewUserFromOzwilloResponseWithCorrectCollectivite()
    {
        $email = 'new@user.com';
        $username = 'newUsername';
        $authData = $this->getAuthUserData($email, 'new family name', 'new name');
        $userProviderResponseMock = $this->createUserResponseMock();
        $userProviderResponseMock
            ->expects($this->once())
            ->method('getResourceOwner')
            ->will($this->returnValue($this->createResourceOwnerMock('ozwillo-client-id')));

        $userProviderResponseMock
            ->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $userProviderResponseMock
            ->expects($this->once())
            ->method('getRealName')
            ->will($this->returnValue($username));
        $userProviderResponseMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($authData));

        $result = $this->userProvider->loadUserByOAuthUserResponse($userProviderResponseMock);
        self::assertInstanceOf(User::class, $result);

        /**
         * check database
         */
        $this->em->clear();
        $dbUser = $this->em->getRepository(User::class)->find($result->getId());
        self::assertEquals('cbb67b1d-612b-4a6e-b2c3-e1b215fe685a', $dbUser->getOzwilloId());
        self::assertEquals('newUsername', $dbUser->getUsername());
        self::assertEquals('new@user.com', $dbUser->getEmail());
        self::assertEquals('new family name', $dbUser->getNom());
        self::assertEquals('new name', $dbUser->getPrenom());
        self::assertCount(1, $dbUser->getCollectivities());
        $collectivity = $dbUser->getCollectivities()->first();
        self::assertEquals($this->fixtures->getReference('collectivite-one')->getId(), $collectivity->getId());
        self::assertEquals('ozwillo-client-id', $collectivity->getOzwillo()->getClientId());
    }

    protected function createUserResponseMock()
    {
        //\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
        $responseMock = $this->getMockBuilder(UserResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $responseMock;
    }

    /**
     * create the mock of OzwilloResourceOwner
     *
     * @param string $clientId default will return ozwillo-client-id from collectivite_ozwillo fixture
     *
     * @return OzwilloResourceOwner|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createResourceOwnerMock($clientId = 'ozwillo-client-id')
    {
        $resourceOwnerMock = $this->getMockBuilder(OzwilloResourceOwner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceOwnerMock
            ->expects($this->once())
            ->method('getOption')
            ->with('client_id')
            ->will($this->returnValue($clientId));

        return $resourceOwnerMock;
    }

    protected function getAuthUserData($email, $familyName, $givenName, $ozwilloUserId = 'cbb67b1d-612b-4a6e-b2c3-e1b215fe685a')
    {
        return [
            'name' => 'the name',
            'family_name' => $familyName,
            'given_name' => $givenName,
            'nickname' => 'name',
            'zoneinfo' => 'Europe/Paris',
            'locale' => 'fr',
            'email' => $email,
            'email_verified' => true,
            'updated_at' => 1524644216,
            'sub' => $ozwilloUserId,
        ];
    }

}