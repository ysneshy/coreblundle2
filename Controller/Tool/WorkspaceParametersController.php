<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Form\PartialWorkspaceImportType;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;
    private $localeManager;
    private $userManager;
    private $tosManager;
    private $utilities;
    private $groupManager;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "security"            = @DI\Inject("security.context"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "router"              = @DI\Inject("router"),
     *     "localeManager"       = @DI\Inject("claroline.common.locale_manager"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "tosManager"          = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "utilities"           = @DI\Inject("claroline.utilities.misc"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request,
        LocaleManager $localeManager,
        UserManager $userManager,
        GroupManager $groupManager,
        TermsOfServiceManager $tosManager,
        ClaroUtilities $utilities,
        ToolManager $toolManager
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->tosManager = $tosManager;
        $this->utilities = $utilities;
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/form/export",
     *     name="claro_workspace_export_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceExportFormAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/export",
     *     name="claro_workspace_export"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function workspaceExportAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->workspaceManager->export($workspace, $name);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/editform",
     *     name="claro_workspace_edit_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceEditFormAction(Workspace $workspace)
    {
        $user = $this->security->getToken()->getUser();
        $this->checkAccess($workspace);
        $username = is_null($workspace->getCreator()) ? '' : $workspace->getCreator()->getUsername();
        $creationDate = is_null($workspace->getCreationDate()) ? null : $this->utilities->intlDateFormat(
            $workspace->getCreationDate()
        );
        $count = $this->workspaceManager->countUsers($workspace->getId());
        $storageUsed = $this->workspaceManager->getUsedStorage($workspace);
        $storageUsed = $this->utilities->formatFileSize($storageUsed);
        $countResources = $this->workspaceManager->countResources($workspace);
        $workspaceAdminTool = $this->toolManager->getAdminToolByName('workspace_management');
        $isAdmin = $this->security->isGranted('OPEN', $workspaceAdminTool);

        $form = $this->formFactory->create(
            FormFactory::TYPE_WORKSPACE_EDIT,
            array(
                $username,
                $creationDate,
                $count,
                $storageUsed,
                $countResources,
                $isAdmin
            ),
            $workspace
        );

        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                array('workspace' => $workspace->getId()),
                true
            );
        } else {
            $url = '';
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'url' => $url,
            'user' => $user,
            'count' => $count
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/edit",
     *     name="claro_workspace_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceEditAction(Workspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $wsRegisteredDisplayable = $workspace->isDisplayable();
        $workspaceAdminTool = $this->toolManager->getAdminToolByName('workspace_management');
        $isAdmin = $this->security->isGranted('OPEN', $workspaceAdminTool);
        $form = $this->formFactory->create(
            FormFactory::TYPE_WORKSPACE_EDIT,
            array(null, null, null, null, null, $isAdmin),
            $workspace
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            //throw new \Exception($workspace->getMaxStorageSize());
            $this->workspaceManager->editWorkspace($workspace);
            $this->workspaceManager->rename($workspace, $workspace->getName());
            $displayable = $workspace->isDisplayable();

            if (!$displayable && $displayable !== $wsRegisteredDisplayable) {
                $this->workspaceTagManager->deleteAllAdminRelationsFromWorkspace($workspace);
            }

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspace->getId(),
                        'toolName' => 'parameters'
                    )
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
            $workspace->setCode($wsRegisteredCode);
        }

        $user = $this->security->getToken()->getUser();

        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                array('workspace' => $workspace->getId()),
                true
            );
        } else {
            $url = '';
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'url' => $url,
            'user' => $user
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     *
     * @param Workspace $workspace
     * @param Tool              $tool
     *
     * @return Response
     */
    public function openWorkspaceToolConfig(Workspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $event = $this->eventDispatcher->dispatch(
            strtolower('configure_workspace_tool_' . $tool->getName()),
            'ConfigureWorkspaceTool',
            array($tool, $workspace)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate",
     *     name="claro_workspace_subscription_url_generate"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function urlSubscriptionGenerateAction(Workspace $workspace)
    {
        $user = $this->security->getToken()->getUser();

        if ( $user === 'anon.') {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_subscription_url_generate_anonymous',
                    array(
                        'workspace' => $workspace->getId(),
                        'toolName' => 'home'
                    )
                )
            );
        }

        $this->workspaceManager->addUserAction($workspace, $user);

        return $this->redirect(
            $this->generateUrl(
                'claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home')
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate/anonymous",
     *     name="claro_workspace_subscription_url_generate_anonymous"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription_anonymous.html.twig")
     *
     * @param Workspace $workspace
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function anonymousSubscriptionAction(Workspace $workspace)
    {
        if (!$workspace->getSelfRegistration()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(
            FormFactory::TYPE_USER_BASE_PROFILE, array($this->localeManager, $this->tosManager)
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->userManager->createUser($user);
            $this->workspaceManager->addUserAction($workspace, $user);

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home')
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/import/partial/form",
     *     name="claro_workspace_partial_import_form"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:importForm.html.twig")
     * @param Workspace $workspace
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function importFormAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->container->get('form.factory')->create(new PartialWorkspaceImportType());

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/import/partial/submit",
     *     name="claro_workspace_partial_import_submit"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:importForm.html.twig")
     * @param Workspace $workspace
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function importAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->container->get('form.factory')->create(new PartialWorkspaceImportType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $template = $form->get('workspace')->getData();
            $config = Configuration::fromTemplate($template);
            $this->workspaceManager->importInExistingWorkspace($config, $workspace);
        }

        return new Response('YOLOOOOLOOLLO');
    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
