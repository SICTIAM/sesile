<?php

namespace Sesile\MigrationBundle\Migrator;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MigrationBundle\Service\LegacyUserService;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use Sesile\UserBundle\Entity\User;

/**
 * Class UserMigrator
 * @package Sesile\MigrationBundle\Migrator
 */
class UserMigrator implements SesileMigratorInterface
{
    /**
     * @var LegacyUserService
     */
    protected $service;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var FosUserManager
     */
    protected $fosUserManager;

    /**
     * UserMigrator constructor.
     * @param LegacyUserService $legacyUserService
     * @param FosUserManager $userManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        LegacyUserService $legacyUserService,
        FosUserManager $userManager,
        LoggerInterface $logger
    ) {
        $this->service = $legacyUserService;
        $this->fosUserManager = $userManager;
        $this->logger = $logger;
    }

    /**
     * @param Collectivite $collectivite
     * @param $oldCollectivityId
     * @return Message
     */
    public function migrate(Collectivite $collectivite, $oldCollectivityId)
    {

        $this->logger->info(
            sprintf('[USER_MIGRATOR] START for legacy collectivity SIREN: %s', $collectivite->getSiren())
        );
        try {
            $legacyUsers = $this->service->getLegacyUsersByCollectivity($oldCollectivityId);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    '[USER_MIGRATOR] Error on getting the legacy users for collectivity %s : %s',
                    $oldCollectivityId,
                    $e->getMessage()
                )
            );

            return new Message(
                false,
                null,
                [
                    sprintf(
                        '[USER_MIGRATOR] Error on getting the legacy users for collectivity %s : %s',
                        $oldCollectivityId,
                        $e->getMessage()
                    ),
                ]
            );
        }
        if (!$legacyUsers || count($legacyUsers) < 1) {
            $this->logger->warning(
                sprintf('[USER_MIGRATOR] legacy users for collectivity %s not found.', $oldCollectivityId)
            );

            return new Message(
                false,
                null,
                [sprintf('[USER_MIGRATOR] legacy users for collectivity %s not found.', $oldCollectivityId)]
            );
        }
        $usersCollection = $this->handleUsers($legacyUsers, $collectivite);

        $this->logger->info(sprintf('[USER_MIGRATOR] %s legacy users successfully imported for collectivity %s.', $usersCollection->count(), $oldCollectivityId));

        return new Message(true, $usersCollection);
    }

    /**
     * @param $legacyUsers
     * @param $collectivite
     *
     * @return ArrayCollection
     */
    protected function handleUsers($legacyUsers, $collectivite)
    {
        $usersCollection = new ArrayCollection();
        foreach ($legacyUsers as $legacyUser) {
            $newUser = $this->buildUser($collectivite, $legacyUser);
            $this->fosUserManager->updateUser($newUser);
            $usersCollection->add($newUser);
        }

        return $usersCollection;
    }

    /**
     * @param Collectivite $collectivite
     * @param $legacyUser
     * @return User
     */
    private function buildUser(Collectivite $collectivite, $legacyUser)
    {
        $user = new User();
        $user->setCollectivite($collectivite);
        $user->setUsername($legacyUser['username']);
        $user->setUsernameCanonical($legacyUser['username_canonical']);
        $user->setEmail($legacyUser['email']);
        $user->setEmailCanonical($legacyUser['email_canonical']);
        $user->setEnabled((bool)$legacyUser['enabled']);
        $user->setSalt($legacyUser['salt']);
        $user->setPassword($legacyUser['password']);
//        if (isset($legacyUser['last_login'])) {
//            new \DateTime($legacyUser['last_login'])
//            $user->setLastLogin($legacyUser['last_login']);
//        }
        $user->setConfirmationToken($legacyUser['confirmation_token']);
        $user->setPasswordRequestedAt($legacyUser['password_requested_at']);
//      $user  ->setRoles(array('a:1:{i:0;s:10:"ROLE_ADMIN"}'));
        $role = unserialize($legacyUser['roles']);
        $user->setRoles($role);
        $user->setNom($legacyUser['Nom']);
        $user->setPrenom($legacyUser['Prenom']);
        $user->setPath($legacyUser['path']);
        $user->setVille($legacyUser['ville']);
        $user->setCp($legacyUser['code_postal']);
        $user->setPays($legacyUser['pays']);
        $user->setDepartement($legacyUser['departement']);
        $user->setRole($legacyUser['role']);
        $user->setApitoken($legacyUser['apitoken']);
        $user->setApisecret($legacyUser['apisecret']);
        $user->setApiactivated((bool) $legacyUser['apiactivated']);
        $user->setPathSignature($legacyUser['pathSignature']);
        $user->setQualite($legacyUser['qualite']);
        $user->setSesileVersion('4.0');
        $user->setOzwilloId(null);
//        $user->addUserrole(UserFixtures::aValidUserRole($user));
//        $user->addUserrole(UserFixtures::aValidUserRole($user, 'Développeur'));
        return $user;
    }

}