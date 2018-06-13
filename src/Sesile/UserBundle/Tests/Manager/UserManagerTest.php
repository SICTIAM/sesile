<?php


namespace Sesile\UserBundle\Tests\Manager;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use Sesile\MainBundle\DataFixtures\CollectiviteFixtures;
use Sesile\MainBundle\DataFixtures\UserFixtures;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;
use Sesile\UserBundle\Entity\User;

class UserManagerTest extends SesileWebTestCase
{
    /**
     * @var FosUserManager
     */
    protected $fosUserManager;
    /**
     * @var
     */
    protected $userManager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->fosUserManager = $this->getContainer()->get('fos_user.user_manager');
//        $this->userManager  = $this->getContainer()->get('sesile.user.manager');
        $this->fixtures = $this->loadFixtures(
            [
                CollectiviteFixtures::class
            ]
        )->getReferenceRepository();
        parent::setUp();
    }

    public function testSaveBareUser()
    {
//        $roles = 'a:1:{i:0;s:10:"ROLE_ADMIN";}';
//        var_dump(unserialize($roles));
        $collectivite = $this->fixtures->getReference('collectivite-one');
        $user = new User();
        $user->setCollectivite($collectivite);
        $user->setUsername('username');
        $user->setUsernameCanonical('username');
        $user->setEmail('user@domain.com');
        $user->setEmailCanonical('user1@domain.com');
        $user->setEnabled(true);
        $user->setSalt('59du74lhbh0c0kw0cw004g4wkkgssw4');
        $user->setPassword('60cBrdEYc30Ck0w-AmBzZelMmnCqYZDsApBu87RxYihnVUlGms/WsA+jA01kfvsG9NoSywAAYrNWklR1EyZyDxg==');
        $user->setLastLogin(null);
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
//      $user  ->setRoles(array('a:1:{i:0;s:10:"ROLE_ADMIN"}'));
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setNom('nom1');
        $user->setPrenom('prenom1');
        $user->setPath('118a36c5fcfb9d9ac2ef1510204cb15728639b0f.png');
        $user->setVille('Saint Sauveur sur Tinée');
        $user->setCp('06420');
        $user->setPays('FRANCE');
        $user->setDepartement('06');
        $user->setRole('Maire');
        $user->setApitoken('token_09b7cedb5f9a6df29468b9ddf490ed70');
        $user->setApisecret('secret_abf9411ade3787a8e668ac534f97cf1a');
        $user->setApiactivated(true);
        $user->setPathSignature('91fae0fa3ec8f0c6b7d455fab850a595e8ae45ae.jpeg');
        $user->setQualite('Directeur');
        $user->setSesileVersion('3.5');
        $user->setOzwilloId(null);
        $user->addUserrole(UserFixtures::aValidUserRole($user));
        $user->addUserrole(UserFixtures::aValidUserRole($user, 'Développeur'));
        $this->fosUserManager->updateUser($user);
        //get from db
        $this->em->clear();
        $newUser = $this->em->getRepository(User::class)->findOneBy(['username' => 'username']);
        self::assertEquals('username', $newUser->getUsernameCanonical());
        self::assertEquals('user@domain.com', $newUser->getEmail());
        self::assertEquals('user@domain.com', $newUser->getEmailCanonical());
        self::assertTrue($newUser->isEnabled());
        self::assertEquals('59du74lhbh0c0kw0cw004g4wkkgssw4', $newUser->getSalt());
        self::assertEquals('60cBrdEYc30Ck0w-AmBzZelMmnCqYZDsApBu87RxYihnVUlGms/WsA+jA01kfvsG9NoSywAAYrNWklR1EyZyDxg==', $newUser->getPassword());
        self::assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $newUser->getRoles());
//        self::assertEquals('a:1:{i:0;s:10:"ROLE_ADMIN";}', serialize($newUser->getRoles()));
        self::assertEquals('a:2:{i:0;s:10:"ROLE_ADMIN";i:1;s:9:"ROLE_USER";}', serialize($newUser->getRoles()));
        self::assertEquals('nom1', $newUser->getNom());
        self::assertEquals('prenom1', $newUser->getPrenom());
        self::assertEquals('118a36c5fcfb9d9ac2ef1510204cb15728639b0f.png', $newUser->getPath());
        self::assertEquals('Saint Sauveur sur Tinée', $newUser->getVille());
        self::assertEquals('06420', $newUser->getCp());
        self::assertEquals('FRANCE', $newUser->getPays());
        self::assertEquals('06', $newUser->getDepartement());
        self::assertEquals('Maire', $newUser->getRole());
        self::assertEquals('token_09b7cedb5f9a6df29468b9ddf490ed70', $newUser->getApitoken());
        self::assertEquals('secret_abf9411ade3787a8e668ac534f97cf1a', $newUser->getApisecret());
        self::assertTrue($newUser->getApiactivated());
        self::assertEquals('91fae0fa3ec8f0c6b7d455fab850a595e8ae45ae.jpeg', $newUser->getPathSignature());
        self::assertEquals('Directeur', $newUser->getQualite());
        self::assertEquals('3.5', $newUser->getSesileVersion());
        self::assertNull($newUser->getOzwilloId());
    }

}