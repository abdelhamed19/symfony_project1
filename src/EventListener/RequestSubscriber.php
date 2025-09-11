<?php

namespace App\EventListener;

use App\Services\SystemLanguageService;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private LocaleSwitcher $localeSwitcher,
        private SystemLanguageService $languageService
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->headers->get('X-Locale', 'en');

        if (!in_array($locale, $this->languageService->getLocales())) {
            $locale = 'en';
        }

        $this->localeSwitcher->setLocale($locale);
    }

}
