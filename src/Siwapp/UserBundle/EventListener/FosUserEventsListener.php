<?php

namespace Siwapp\UserBundle\EventListener;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for FOSUserEvents.
 */
class FosUserEventsListener implements EventSubscriberInterface
{

    public function onProfileEditCompleted(UserEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        $user = $event->getUser();
        if ($user->getLocale() !== null) {
            $request->getSession()->set('_locale', $user->getLocale());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::PROFILE_EDIT_COMPLETED => [['onProfileEditCompleted']],
        ];
    }
}
