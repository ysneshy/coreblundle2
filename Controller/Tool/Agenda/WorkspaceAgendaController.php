<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Manager\Exception\NoEventFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller of the agenda
 */
class WorkspaceAgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $om;
    private $request;
    private $agendaManager;
    private $router;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/show",
     *     name="claro_workspace_agenda_show",
     *     options = {"expose"=true}
     * )
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $data = $this->agendaManager->displayEvents($workspace);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tasks",
     *     name="claro_workspace_agenda_tasks"
     * )
     *
     * @param Workspace $workspace
     *
     * @EXT\Template("ClarolineCoreBundle:Agenda:tasks.html.twig")
     */
    public function tasksAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $events = $this->om->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspace->getId(), true);

        return array('events' => $this->agendaManager->convertEventsToArray($events));
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/import/modal/form",
     *     name="claro_workspace_agenda_import_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\agenda:importIcsModalForm.html.twig")
     * @return array
     */
    public function importEventsModalForm(Workspace $workspace)
    {
        $this->checkAccess($workspace, 'edit');
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/import",
     *     name="claro_workspace_agenda_import"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\agenda:importIcsModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function importsEventsIcsAction(Workspace $workspace)
    {
        $this->checkAccess($workspace, 'edit');
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $events = $this->agendaManager->importEvents($form->get('file')->getData(), $workspace);

            return new JsonResponse($events, 200);
        }

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/add/event/form",
     *     name="claro_workspace_agenda_add_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Agenda:addEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function addEventModalFormAction(Workspace $workspace)
    {
        $this->checkAccess($workspace, 'edit');
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'action' => $this->router->generate(
                'claro_workspace_agenda_add_event', array('workspace' => $workspace->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/add",
     *     name="claro_workspace_agenda_add_event"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Agenda:addEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEventAction(Workspace $workspace)
    {
        $this->checkAccess($workspace, 'edit');
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $form->getData();
            $data = $this->agendaManager->addEvent($event, $workspace);

            return new JsonResponse(array($data), 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'action' => $this->router->generate(
                'claro_workspace_agenda_add_event', array('workspace' => $workspace->getId())
            )
        );
    }

    //checks the access of the agenda tool
    private function checkAccess($workspace, $action = 'open')
    {
        if (!$this->security->isGranted(array('agenda', $action), $workspace)) {
            throw new AccessDeniedException("The event cannot be updated");
        }
    }
}
