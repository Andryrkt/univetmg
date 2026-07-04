<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class SecuritySubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        $email = method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->toString();
        
        $this->logger->info(sprintf('Successful login for user: %s', $email));
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        $username = 'unknown';

        if ($passport && $passport->hasBadge(UserBadge::class)) {
            $username = $passport->getBadge(UserBadge::class)->getUserIdentifier();
        }

        $this->logger->warning(sprintf('Authentication failed for user: %s', $username));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }
}
