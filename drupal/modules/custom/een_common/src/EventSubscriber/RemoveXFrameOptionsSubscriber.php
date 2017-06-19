<?php

namespace Drupal\een_common\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RemoveXFrameOptionsSubscriber
 * @package Drupal\een_common\EventSubscriber
 */
class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        $events[KernelEvents::RESPONSE][] = array('RemoveXFrameOptions', -10);
        return $events;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function RemoveXFrameOptions(FilterResponseEvent $event)
    {
        if (\Drupal::request()->getRequestUri() === '/opportunities/widget?search=car&country%5B%5D=UK') {
            $response = $event->getResponse();
            $response->headers->remove('X-Frame-Options');
        }
    }
}