<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Form\UserPublicProfileUrlType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $security;
    private $request;
    private $localeManager;
    private $encoderFactory;
    private $toolManager;
    private $facetManager;
    private $ch;
    private $authenticationManager;

    /**
     * @DI\InjectParams({
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"               = @DI\Inject("security.context"),
     *     "request"                = @DI\Inject("request"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "encoderFactory"         = @DI\Inject("security.encoder_factory"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "ch"                     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        Request $request,
        LocaleManager $localeManager,
        EncoderFactory $encoderFactory,
        ToolManager $toolManager,
        FacetManager $facetManager,
        PlatformConfigurationHandler $ch,
        AuthenticationManager $authenticationManager
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->encoderFactory = $encoderFactory;
        $this->toolManager = $toolManager;
        $this->facetManager = $facetManager;
        $this->ch = $ch;
        $this->authenticationManager = $authenticationManager;
    }

    private function isInRoles($role, $roles)
    {
        foreach ($roles as $current) {
            if ($role->getId() == $current->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @EXT\Route(
     *     "/",
     *      name="claro_profile_view"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function viewAction(User $loggedUser)
    {
        $facets = $this->facetManager->getPrivateVisibleFacets();
        $fieldFacetValues = $this->facetManager->getFieldValuesByUser($loggedUser);
        $fieldFacets = $this->facetManager->getPrivateVisibleFields();

        return array(
            'user'  => $loggedUser,
            'facets' => $facets,
            'fieldFacetValues' => $fieldFacetValues,
            'fieldFacets' => $fieldFacets
        );
    }

    /**
     * @EXT\Route(
     *     "/{publicUrl}",
     *      name="claro_public_profile_view",
     *      options={"expose"=true}
     * )
     */
    public function publicProfileAction($publicUrl)
    {
        $isAccessibleForAnon = $this->ch->getParameter('anonymous_public_profile');

        if (!$isAccessibleForAnon && $this->security->getToken()->getUser() === 'anon.') {
            throw new AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByIdOrPublicUrl($publicUrl);

        try {
            /** @var \Claroline\CoreBundle\Entity\User $user */
            $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByIdOrPublicUrl($publicUrl);
        } catch (NoResultException $e) {
            throw new NotFoundHttpException("Page not found");
        }

        $facets = $this->facetManager->getVisibleFacets($this->security->getToken());
        $fieldFacetValues = $this->facetManager->getFieldValuesByUser($user);
        $publicProfilePreferences = $this->facetManager->getVisiblePublicPreference();
        $fieldFacets = $this->facetManager->getVisibleFieldFacets($this->security->getToken());

        $response = new Response(
            $this->renderView(
                'ClarolineCoreBundle:Profile:publicProfile.html.twig',
                array(
                    'user' => $user,
                    'publicProfilePreferences' => $publicProfilePreferences,
                    'facets' => $facets,
                    'fieldFacetValues' => $fieldFacetValues,
                    'fieldFacets' => $fieldFacets
                )
            )
        );

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/profile/edit/{user}",
     *     name="claro_user_profile_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     *
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editProfileAction(User $loggedUser, User $user = null)
    {
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $isGrantedUserAdmin = $this->get('security.context')->isGranted(
            'OPEN', $this->toolManager->getAdminToolByName('user_management')
        );
        $editYourself = false;

        if (null !== $user && !$isAdmin && !$isGrantedUserAdmin) {
            throw new AccessDeniedException();
        }

        if (null === $user) {
            $user = $loggedUser;
            $editYourself = true;
        }
        $userRole = $this->roleManager->getUserRoleByUser($user);
        $roles = $this->roleManager->getPlatformRoles($user);

        $form = $this->createForm(
            new ProfileType(
                $roles,
                $isAdmin,
                $isGrantedUserAdmin,
                $this->localeManager->getAvailableLocales(),
                $this->authenticationManager->getDrivers()
            ),
            $user
        );

        $form->handleRequest($this->request);
        $unavailableRoles = [];

        if ($this->get('request')->getMethod() === 'POST') {
            $roles = ($isAdmin || $isGrantedUserAdmin) ?
                $form->get('platformRoles')->getData() :
                array($this->roleManager->getRoleByName('ROLE_USER'));
        } else {
            $roles = ($isAdmin || $isGrantedUserAdmin) ?
                $this->roleManager->getAllPlatformRoles() :
                array($this->roleManager->getRoleByName('ROLE_USER'));
        }

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert($user, $role);
            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        if ($form->isValid() && count($unavailableRoles) === 0) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            $user = $form->getData();
            $this->userManager->rename($user, $user->getUsername());
            $this->roleManager->renameUserRole($userRole, $user->getUsername());

            $successMessage = $translator->trans('edit_profile_success', array(), 'platform');
            $errorMessage   = $translator->trans('edit_profile_error', array(), 'platform');
            $errorRight = $translator->trans('edit_profile_error_right', array(), 'platform');
            $redirectUrl = $this->generateUrl('claro_admin_user_list');

            if ($editYourself) {
                $successMessage = $translator->trans('edit_your_profile_success', array(), 'platform');
                $errorMessage   = $translator->trans('edit_your_profile_error', array(), 'platform');
                $redirectUrl    = $this->generateUrl('claro_profile_view');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $unitOfWork    = $entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles  = array();

            if (isset($form['platformRoles'])) {
                //verification:
                //only the admin can grant the role admin
                //simple users cannot change anything. Don't let them put whatever they want with a fake form.
                $newRoles = $form['platformRoles']->getData();
                $this->userManager->setPlatformRoles($user, $newRoles);
            }

            $rolesChangeSet = array();
            //Detect added
            foreach ($newRoles as $role) {
                if (!$this->isInRoles($role, $roles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(false, true);
                }
            }
            //Detect removed
            foreach ($roles as $role) {
                if (!$this->isInRoles($role, $newRoles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(true, false);
                }
            }
            if (count($rolesChangeSet) > 0) {
                $changeSet['roles'] = $rolesChangeSet;
            }

            if ($this->userManager->uploadAvatar($user) === false ) {
                $sessionFlashBag->add('error', $errorRight);
            }

            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdate',
                array($user, $changeSet)
            );

            $sessionFlashBag->add('success', $successMessage);

            return $this->redirect($redirectUrl);
        }

        return array(
            'form'             => $form->createView(),
            'user'             => $user,
            'editYourself'     => $editYourself,
            'unavailableRoles' => $unavailableRoles
        );
    }

    /**
     * @EXT\Route(
     *     "/password/edit",
     *      name="claro_user_password_edit"
     * )
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function editPasswordAction(User $loggedUser)
    {
        $form = $this->createForm(new ResetPasswordType(true));
        $oldPassword = $loggedUser->getPassword();
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');
            $loggedUser->setPlainPassword($form['password']->getData());

            if ($this->encodePassword($loggedUser) === $oldPassword) {
                $loggedUser->setPlainPassword($form['plainPassword']->getData());
                $loggedUser->setPassword($this->encodePassword($loggedUser));
                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($loggedUser);
                $entityManager->flush();
                $sessionFlashBag->add('success', $translator->trans('edit_password_success', array(), 'platform'));
            } else {
                $sessionFlashBag->add('error', $translator->trans('edit_password_error_current', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        return array(
            'form' => $form->createView(),
            'user' => $loggedUser
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/edit",
     *      name="claro_user_public_url_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPublicUrlAction(User $loggedUser)
    {
        $currentPublicUrl = $loggedUser->getPublicUrl();
        $form = $this->createForm(new UserPublicProfileUrlType(), $loggedUser);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            try {
                /** @var \Claroline\CoreBundle\Entity\User $user */
                $user = $form->getData();

                $user->setHasTunedPublicUrl(true);

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('tune_public_url_success', array(), 'platform'));
            } catch (\Exception $exception) {
                $sessionFlashBag->add('error', $translator->trans('tune_public_url_error', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        return array(
            'form'             => $form->createView(),
            'user'             => $loggedUser,
            'currentPublicUrl' => $currentPublicUrl
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/check",
     *      name="claro_user_public_url_check"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Method({"POST"})
     */
    public function checkPublicUrlAction(Request $request)
    {
        $existedUser = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl(
            $request->request->get('publicUrl')
        );
        $data = array('check' => false);

        if (null === $existedUser) {
            $data['check'] = true;
        }

        $response = new JsonResponse($data);

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/facet/{facet}/edit",
     *      name="claro_user_facet_edit"
     * )
     * @EXT\Method({"POST"})
     */
    public function editFacet(User $user, Facet $facet)
    {
        //do some validation
        $data = $this->request->request;

        foreach ($data as $key => $value) {
            $fieldFacetId = (int) str_replace('field-', '', $key);
            $fieldFacet = $this->facetManager->getFieldFacet($fieldFacetId);
            $this->facetManager->setFieldValue($user, $fieldFacet, reset($value));
        }

        $fieldFacetValues = $this->facetManager->getFieldValuesByUser($user);
        $data = array();

        foreach ($fieldFacetValues as $fieldFacetValue) {
            $data[$fieldFacetValue->getFieldFacet()->getId()] = $this->facetManager->getDisplayedValue(
                $fieldFacetValue
            );
        }

        return new JsonResponse($data);
    }

    private function encodePassword(User $user)
    {
        return $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());
    }
}
