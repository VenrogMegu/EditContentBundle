<?php

namespace Tgc\EditContentBundle\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LocaleSubscriber implements EventSubscriberInterface {

    use ContainerAwareTrait;

    private $defaultLocale;

    public function __construct($defaultLocale = 'fr') {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        if ($request->getSession()->get('_locale')) {
            $prefLanguage = $request->getSession()->get('_locale');
        } else {
            $prefLanguage = $request->getPreferredLanguage();
        }


        if ($prefLanguage == 'fr' | $prefLanguage == 'en') {
            $request->setLocale($request->getPreferredLanguage([$prefLanguage]));
        } else {
            $request->setLocale($request->getSession()->get('_locale', $request->getPreferredLanguage(['fr'])));
        }
    }

    public static function getSubscribedEvents() {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }

}
