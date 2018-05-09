<?php

namespace Sesile\UserBundle\Security\Core\User;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Sesile\MainBundle\Manager\CollectiviteManager;
use Sesile\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Util\TokenGenerator;

class MyFOSUBUserProvider extends BaseFOSUBProvider
{
    protected $tokenGenerator;
    /**
     * @var CollectiviteManager
     */
    protected $collectiviteManager;

    public function __construct(UserManagerInterface $userManager, array $properties, CollectiviteManager $collectiviteManager, TokenGenerator $tokenGenerator)
    {
        parent::__construct($userManager, $properties);
        $this->collectiviteManager = $collectiviteManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {

        // get property from provider configuration by provider name
        // , it will return `facebook_id` in that case (see service definition below)
        $property = $this->getProperty($response);
        $username = $response->getUsername(); // get the unique user identifier

        //we "disconnect" previously connected users
        $existingUser = $this->userManager->findUserBy(array($property => $username));
        if (null !== $existingUser) {
            // set current user id and token to null for disconnect
            // ...

            $this->userManager->updateUser($existingUser);
        }
        // we connect current user, set current user id and token
        // ...
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userEmail = $response->getEmail();
        $data = $response->getData();
        $user = $this->userManager->findUserByEmail($userEmail);

        // if null just create new user and set it properties
        if (null === $user) {
            $username = $response->getRealName();
            $client_id = $response->getResourceOwner()->getOption('client_id');
            //$client_id is the unique id for a collectivite
//            $ozwilloCollectivite = $this->em->getRepository('SesileMainBundle:CollectiviteOzwillo')->findOneByClientId($client_id);
            $ozwilloCollectiviteResult = $this->collectiviteManager->getOzwilloCollectivityByClientId($client_id);
            if (false === $ozwilloCollectiviteResult->isSuccess()) {
                throw new \Exception('No Collectivity found for this client id. User could not be created');
            }
            $user = new User();
            $user->setUsername($username);
            $user->setOzwilloId($data['sub']);
            $user->setNom($data['family_name']);
            $user->setPrenom($data['given_name']);
            $user->setEmail($userEmail);
            $user->setPassword(substr($this->tokenGenerator->generateToken(), 0, 10));
            $user->setEnabled(true);
            //leave for legacy - @todo remove this line when refactoring
            $user->setCollectivite($ozwilloCollectiviteResult->getData()->getCollectivite());
            //add the colelctivite to the user ref_collecitvite_user table
            $user->addCollectivity($ozwilloCollectiviteResult->getData()->getCollectivite());

            // ... save user to database
            $this->userManager->updateUser($user);

            return $user;
        }
        // else update access token of existing user
//        $serviceName = $response->getResourceOwner()->getName();
//        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
//        $user->$setter($response->getAccessToken());//update access token
        $user->setNom($data['family_name']);
        $user->setPrenom($data['given_name']);
        $user->setOzwilloId($data['sub']);
        $this->userManager->updateUser($user);

        return $user;
    }
}
