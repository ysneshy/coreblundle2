<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\TermsOfServiceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @DI\Service("claroline.authentication_handler")
 */
class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    private $securityContext;
    private $eventDispatcher;
    private $configurationHandler;
    private $templating;
    private $formFactory;
    private $termsOfService;
    private $manager;
    private $router;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "context"                = @DI\Inject("security.context"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configurationHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "templating"             = @DI\Inject("templating"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "termsOfService"         = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "manager"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager")
     * })
     *
     */
    public function __construct(
        SecurityContextInterface $context,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configurationHandler,
        EngineInterface $templating,
        FormFactory $formFactory,
        TermsOfServiceManager $termsOfService,
        ObjectManager $manager,
        Router $router,
        UserManager $userManager
    )
    {
        $this->securityContext = $context;
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationHandler = $configurationHandler;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->termsOfService = $termsOfService;
        $this->manager = $manager;
        $this->router = $router;
        $this->userManager = $userManager;
    }

    /**
     * @DI\Observe("security.interactive_login")
     */
    public function onLoginSuccess(InteractiveLoginEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user->getInitDate() === null) {
            $this->userManager->setUserInitDate($user);
        }

        $this->eventDispatcher->dispatch('log', 'Log\LogUserLogin', array($user));
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->securityContext->setToken($token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($uri = $request->getSession()->get('_security.main.target_path')) {
            return new RedirectResponse($uri);
        }

        if ($this->configurationHandler->getParameter('redirect_after_login') && $user->getLastUri() !== null) {
            return new RedirectResponse($user->getLastUri());
        }

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->configurationHandler->getParameter('terms_of_service')) {
            $this->showTermOfServices($event);
        }
    }

    /**
     * @DI\Observe("kernel.response")
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->configurationHandler->getParameter('redirect_after_login')) {
            $this->saveLastUri($event);
        }
    }

    private function saveLastUri(FilterResponseEvent $event)
    {
        if ($event->isMasterRequest()
            && !$event->getRequest()->isXmlHttpRequest()
            && !in_array($event->getRequest()->attributes->get('_route'), $this->getExcludedRoutes())
            && 'GET' === $event->getRequest()->getMethod()
            && 200 === $event->getResponse()->getStatusCode()
            && !$event->getResponse() instanceof StreamedResponse
        ) {
            if ($token =  $this->securityContext->getToken()) {
                if ('anon.' !== $user = $token->getUser()) {
                    $uri = $event->getRequest()->getRequestUri();
                    $user->setLastUri($uri);
                    $this->manager->persist($user);
                    $this->manager->flush();
                }
            }
        }
    }

    private function showTermOfServices(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()
            && ($user = $this->getUser($event->getRequest()))
            && !$user->hasAcceptedTerms()
            && !$this->isImpersonated()
            && ($content = $this->termsOfService->getTermsOfService(false))
        ) {
            if (($termsOfService = $event->getRequest()->get('accept_terms_of_service_form'))
                && isset($termsOfService['terms_of_service'])
            ) {
                $user->setAcceptedTerms(true);
                $this->manager->persist($user);
                $this->manager->flush();
            } else {
                $form = $this->formFactory->create(new TermsOfServiceType(), $content);
                $response = $this->templating->render(
                    'ClarolineCoreBundle:Authentication:termsOfService.html.twig',
                    array('form' => $form->createView())
                );

                $event->setResponse(new Response($response));
            }
        }
    }

    /**
     * Return a user if need to accept the terms of service
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Claroline\CoreBundle\Entity\User
     */
    private function getUser(Request $request)
    {
        if ($this->configurationHandler->getParameter('terms_of_service')
            && $request->get('_route') !== 'claroline_locale_change'
            && $request->get('_route') !== 'claroline_locale_select'
            && $request->get('_route') !== 'bazinga_exposetranslation_js'
            && ($token = $this->securityContext->getToken())
            && ($user = $token->getUser())
            && $user instanceof User
        ) {
            return $user;
        }
    }

    private function getExcludedRoutes()
    {
        return array(
            'bazinga_exposetranslation_js',
            'login_check',
            'login'
        );
    }

    public function isImpersonated()
    {
        if ($this->securityContext->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($this->securityContext->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }
    }
}
