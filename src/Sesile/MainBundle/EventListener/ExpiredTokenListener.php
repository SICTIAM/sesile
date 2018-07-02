<?php


namespace Sesile\MainBundle\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ExpiredTokenListener
 * Listener to the event ozwillo_token.expired
 *
 * @package Sesile\MainBundle\EventListener
 */
class ExpiredTokenListener
{
    /**
     * @var SessionInterface
     */
    protected $session;
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExpiredTokenListener constructor.
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     */
    public function onTokenExpired(Event $event)
    {
        $this->logger->info(sprintf('INVALIDATE OZWILLO TOKEN'));
        $this->tokenStorage->setToken(null);
        $this->session->clear();
        $this->session->invalidate();
    }
}