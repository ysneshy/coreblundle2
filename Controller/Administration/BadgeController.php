<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Entity\User;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller of the badges.
 *
 * @Route("/admin/badges")
 */
class BadgeController extends Controller
{
    /**
     * @Route(
     *     "/{badgePage}/{claimPage}/{userPage}",
     *     name="claro_admin_badges",
     *     requirements={"badgePage" = "\d+", "userPage" = "\d+", "claimPage" = "\d+"},
     *     defaults={"badgePage" = 1, "claimPage" = 1, "userPage" = 1}
     * )
     *
     * @Template
     */
    public function listAction($badgePage = 1, $claimPage = 1, $userPage = 1)
    {
        $this->checkOpen();

        $parameters = array(
            'badgePage'        => $badgePage,
            'claimPage'        => $claimPage,
            'userPage'         => $userPage,
            'add_link'         => 'claro_admin_badges_add',
            'edit_link'        => array(
                'url'    => 'claro_admin_badges_edit',
                'suffix' => '#!edit'
            ),
            'delete_link'      => 'claro_admin_badges_delete',
            'view_link'        => 'claro_admin_badges_edit',
            'current_link'     => 'claro_admin_badges',
            'claim_link'       => 'claro_admin_manage_claim',
            'statistics_link'  => 'claro_admin_badges_statistics',
            'route_parameters' => array()
        );

        return array(
            'parameters'  => $parameters
        );
    }

    /**
     * @Route("/add", name="claro_admin_badges_add")
     *
     * @Template()
     */
    public function addAction(Request $request)
    {
        $this->checkOpen();
        $badge = new Badge();

        $locales = $this->get('claroline.common.locale_manager')->getAvailableLocales();
        foreach ($locales as $locale) {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');

        try {
            if ($this->get('claroline.form_handler.badge')->handleAdd($badge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_add_success_message', array(), 'badge'));

                return $this->redirect($this->generateUrl('claro_admin_badges'));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_add_error_message', array(), 'badge'));

            return $this->redirect($this->generateUrl('claro_admin_badges'));
        }

        return array(
            'form'  => $this->get('claroline.form.badge')->createView(),
            'badge' => $badge
        );
    }

    /**
     * @Route("/edit/{slug}/{page}", name="claro_admin_badges_edit")
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function editAction(Request $request, Badge $badge, $page = 1)
    {
        $this->checkOpen();
        $query   = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge')->findUsers($badge, false);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw $this->createNotFoundException();
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');

        try {
            if ($this->get('claroline.form_handler.badge')->handleEdit($badge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_edit_success_message', array(), 'badge'));

                return $this->redirect($this->generateUrl('claro_admin_badges'));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_edit_error_message', array(), 'badge'));

            return $this->redirect($this->generateUrl('claro_admin_badges'));
        }

        return array(
            'form'  => $this->get('claroline.form.badge')->createView(),
            'badge' => $badge,
            'pager' => $pager
        );
    }

    /**
     * @Route("/delete/{slug}", name="claro_admin_badges_delete")
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function deleteAction(Badge $badge)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badge);
            $entityManager->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_delete_success_message', array(), 'badge'));
        } catch (\Exception $exception) {
            $this->get('session')
                ->getFlashBag()
                ->add('error', $translator->trans('badge_delete_error_message', array(), 'badge'));
        }

        return $this->redirect($this->generateUrl('claro_admin_badges'));
    }

    /**
     * @Route("/award/{slug}", name="claro_admin_badges_award")
     * @ParamConverter("badge", converter="badge_converter")
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     *
     * @Template()
     */
    public function awardAction(Request $request, Badge $badge, User $loggedUser)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $form = $this->createForm($this->get('claroline.form.badge.award'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    $doctrine = $this->getDoctrine();

                    $group   = $form->get('group')->getData();
                    $user    = $form->get('user')->getData();
                    $comment = $form->get('comment')->getData();

                    /** @var \Claroline\CoreBundle\Entity\User[] $users */
                    $users = array();

                    if (null !== $group) {
                        $users = $doctrine->getRepository('ClarolineCoreBundle:User')->findByGroup($group);
                    } elseif (null !== $user) {
                        $users[] = $user;
                    }

                    /** @var \Claroline\CoreBundle\Manager\BadgeManager $badgeManager */
                    $badgeManager = $this->get('claroline.manager.badge');
                    $awardedBadge = $badgeManager->addBadgeToUsers($badge, $users, $comment, $loggedUser);

                    $flashMessageType = 'error';

                    if (0 < $awardedBadge) {
                        $flashMessageType = 'success';
                    }

                    $message = $translator->transChoice(
                        'badge_awarded_count_message',
                        $awardedBadge,
                        array('%awaredBadge%' => $awardedBadge),
                        'badge'
                    );
                    $this->get('session')->getFlashBag()->add($flashMessageType, $message);
                } catch (\Exception $exception) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->get('session')
                            ->getFlashBag()
                            ->add('error', $translator->trans('badge_award_error_message', array(), 'badge'));
                    } else {
                        return new Response($exception->getMessage(), 500);
                    }
                }

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(array('error' => false));
                }

                return $this->redirect($this->generateUrl('claro_admin_badges_edit', array('slug' => $badge->getSlug())));
            }
        }

        return array(
            'badge'  => $badge,
            'form'   => $form->createView()
        );
    }

    /**
     * @Route("/unaward/{slug}/{username}", name="claro_admin_badges_unaward")
     * @ParamConverter("user", options={"mapping": {"username": "username"}})
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function unawardAction(Request $request, Badge $badge, User $user)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            $doctrine = $this->getDoctrine();
            /** @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $doctrine->getManager();

            $userBadge = $doctrine->getRepository('ClarolineCoreBundle:Badge\UserBadge')
                ->findOneByBadgeAndUser($badge, $user);

            $entityManager->remove($userBadge);
            $entityManager->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_unaward_success_message', array(), 'badge'));
        } catch (\Exception $exception) {
            if (!$request->isXmlHttpRequest()) {
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', $translator->trans('badge_unaward_error_message', array(), 'badge'));
            } else {
                return new Response($exception->getMessage(), 500);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('error' => false));
        }

        return $this->redirect($this->generateUrl('claro_admin_badges_edit', array('slug' => $badge->getSlug())));
    }

    /**
     * @Route("/claim/manage/{id}/{validate}", name="claro_admin_manage_claim")
     *
     * @Template()
     */
    public function manageClaimAction(BadgeClaim $badgeClaim, $validate = false)
    {
        $this->checkOpen();

        if (null !== $badgeClaim->getBadge()->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator     = $this->get('translator');
        $successMessage = $translator->trans('badge_reject_award_success_message', array(), 'badge');
        $errorMessage   = $translator->trans('badge_reject_award_error_message', array(), 'badge');

        try {
            if ($validate) {
                $successMessage = $translator->trans('badge_validate_award_success_message', array(), 'badge');
                $errorMessage   = $translator->trans('badge_validate_award_error_message', array(), 'badge');

                /** @var \Claroline\CoreBundle\Manager\BadgeManager $badgeManager */
                $badgeManager = $this->get('claroline.manager.badge');
                $awardedBadge = $badgeManager->addBadgeToUser($badgeClaim->getBadge(), $badgeClaim->getUser());
                if (!$awardedBadge) {
                    $successMessage = $translator->trans('badge_already_award_info_message', array(), 'badge');
                }
            }

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badgeClaim);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $successMessage);
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $errorMessage);
        }

        return $this->redirect($this->generateUrl('claro_admin_badges'));
    }

    /**
     * @Route("/statistics", name="claro_admin_badges_statistics")
     * @Template()
     */
    public function statisticsAction(Request $request)
    {
        $this->checkOpen();

        return array();
    }

    private function checkOpen()
    {
        $badgeAdminTool = $this->container->get('claroline.manager.tool_manager')
            ->getAdminToolByName('badges_management');

        if ($this->container->get('security.context')->isGranted('OPEN', $badgeAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
