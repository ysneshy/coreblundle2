<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @Service("claroline.common.locale_manager")
 */
class LocaleManager
{
    private $defaultLocale;
    private $finder;
    private $locales;
    private $userManager;
    private $context;

    /**
     * @InjectParams({
     *     "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "userManager"    = @Inject("claroline.manager.user_manager"),
     *     "context"        = @Inject("security.context")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler, UserManager $userManager, SecurityContextInterface $context)
    {
        $this->userManager = $userManager;
        $this->defaultLocale = $configHandler->getParameter('locale_language');
        $this->finder = new Finder();
        $this->context = $context;
    }

    /**
     * Get a list of available languages in the platform.
     *
     * @param $path The path of translations files
     *
     * @return Array
     */
    private function retriveAvailableLocales($path = '/../Resources/translations/')
    {
        $locales = array();
        $finder = $this->finder->files()->in(__DIR__.$path)->name('/platform\.[^.]*\.yml/');

        foreach ($finder as $file) {
            $locale = str_replace(array('platform.', '.yml'), '', $file->getRelativePathname());
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    /**
     * Get a list of available languages in the platform.
     *
     * @return Array
     */
    public function getAvailableLocales()
    {
        if (!$this->locales) {
            $this->locales = $this->retriveAvailableLocales();
        }

        return $this->locales;
    }

    /**
     * Set locale setting for current user if this locale is present in the platform
     *
     * @param string $locale The locale string as en, fr, es, etc.
     */
    public function setUserLocale($locale)
    {
        $locales = $this->getAvailableLocales();

        if (isset($locales[$locale]) and ($user = $this->getCurrentUser())) {
            $this->userManager->setLocale($user, $locale);
        }
    }

    /**
     * This method returns the user locale and store it in session, if there is no user this method return default
     * language or the browser language if it is present in translations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string The locale string as en, fr, es, etc.
     */
    public function getUserLocale(Request $request)
    {
        $locales = $this->getAvailableLocales();
        $preferred = explode('_', $request->getPreferredLanguage());

        if ($request->attributes->get('_locale')) {
            $locale = $request->attributes->get('_locale');
        } elseif (($user = $this->getCurrentUser()) &&  $user->getLocale()) {
            $locale = $user->getLocale();
        } elseif ($sessionLocale = $request->getSession()->get('_locale')) {
            $locale = $sessionLocale;
        } elseif (count($preferred) > 0 && isset($locales[$preferred[0]])) {
            $locale = $preferred[0];
        } else {
            $locale = $this->defaultLocale;
        }

        $request->getSession()->set('_locale', $locale);

        return $locale;
    }

    /**
     * Get Current User
     *
     * @return mixed Claroline\CoreBundle\Entity\User or null
     */
    private function getCurrentUser()
    {
        if (is_object($token = $this->context->getToken()) and is_object($user = $token->getUser())) {
            return $user;
        }
    }
}
