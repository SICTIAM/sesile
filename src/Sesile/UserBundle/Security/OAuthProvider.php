<?php


namespace Sesile\UserBundle\Security;


use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider\OAuthProvider as BaseOAuthProvider;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var ResourceOwnerMapInterface
     */
    private $resourceOwnerMap;

    /**
     * @var OAuthAwareUserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @param OAuthAwareUserProviderInterface $userProvider     User provider
     * @param ResourceOwnerMapInterface       $resourceOwnerMap Resource owner map
     * @param UserCheckerInterface            $userChecker      User checker
     * @param TokenStorageInterface           $tokenStorage
     */
    public function __construct(OAuthAwareUserProviderInterface $userProvider, ResourceOwnerMapInterface $resourceOwnerMap, UserCheckerInterface $userChecker, TokenStorageInterface $tokenStorage)
    {

        $this->userProvider = $userProvider;
        $this->resourceOwnerMap = $resourceOwnerMap;
        $this->userChecker = $userChecker;
        $this->tokenStorage = $tokenStorage;
    }
    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        // fix connect to external social very time
        if ($token->isAuthenticated()) {
            return $token;
        }

        /* @var OAuthToken $token */
        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());

        $oldToken = $token->isExpired() ? $this->refreshToken($token, $resourceOwner) : $token;
        $userResponse = $resourceOwner->getUserInformation($oldToken->getRawToken());

        try {
            $user = $this->userProvider->loadUserByOAuthUserResponse($userResponse);
        } catch (OAuthAwareExceptionInterface $e) {
            $e->setToken($oldToken);
            $e->setResourceOwnerName($oldToken->getResourceOwnerName());

            throw $e;
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('loadUserByOAuthUserResponse() must return a UserInterface.');
        }

        $this->userChecker->checkPreAuth($user);
        $this->userChecker->checkPostAuth($user);

        $token = new OAuthToken($oldToken->getRawToken(), $user->getRoles());
        $token->setResourceOwnerName($resourceOwner->getName());
        $token->setUser($user);
        //set collectiviteId into the user token
        $token->setAttribute('orgId', $user->getCurrentOrgId());
        $token->setAuthenticated(true);
        $token->setRefreshToken($oldToken->getRefreshToken());
        $token->setCreatedAt($oldToken->getCreatedAt());

        return $token;
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return
            $token instanceof OAuthToken
            && $this->resourceOwnerMap->hasResourceOwnerByName($token->getResourceOwnerName())
            ;
    }
}