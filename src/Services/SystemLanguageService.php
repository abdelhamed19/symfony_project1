<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SystemLanguageService
{

    private array $languages = [];
    private array $locales = [];
    private ?RequestStack $request = null;

    public function __construct(ParameterBagInterface $parameterBag, RequestStack $request)
    {
        $this->request = $request;
        $languages = $parameterBag->get('languages');
        $out = [];
        foreach ($languages as $locale => $language) {
            $out[$locale] = [
                'name' => $language['name'],
                'locale' => $locale,
            ];
            $this->locales[] = $locale;
        }
        $this->languages = $out;
    }

    public function getLanguageByLocale($locale)
    {
        $languages = $this->getLanguages();
        if (in_array($locale, $this->locales)) {
            return $languages[$locale];
        }
        return null;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getLanguagesFormChoice(): array
    {
        $out = [];
        foreach ($this->languages as $language) {
            $out[$language['name']] = $language['locale'];
        }
        return $out;
    }

    public function getLocales(): array
    {
        $out = [];
        foreach ($this->languages as $language) {
            $out[] = $language['locale'];
        }
        return $out;
    }

    public function getCurrentLocale(): string
    {
        return $this->request->getCurrentRequest()->getLocale() ?? 'en';
    }
}
