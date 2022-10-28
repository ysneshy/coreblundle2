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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\AgendaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller of the Agenda
 */
class AgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $request;
    private $agendaManager;
    private $router;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router"),
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        Request $request,
        AgendaManager $agendaManager,
        RouterInterface $router
    )
    {
        $this->security      = $security;
        $this->formFactory   = $formFactory;
        $this->request       = $request;
        $this->agendaManager = $agendaManager;
        $this->router        = $router;
    }

    /**
     * @EXT\Route(
     *     "/{event}/update/form",
     *     name="claro_agenda_update_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Agenda:updateEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function updateEventModalFormAction(Event $event)
    {
        $this->checkPermission($event);
        $formType = $this->get('claroline.form.agenda');
        $formType->setEditMode();
        $form = $this->createForm($formType, $event);

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }

    /**
     * @EXT\Route(
     *     "/{event}/update",
     *     name="claro_agenda_update"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Agenda:updateEventModalForm.html.twig")
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Event $event)
    {
        $this->checkPermission($event);
        $formType = $this->get('claroline.form.agenda');
        $formType->setEditMode();
        $form = $this->createForm($formType, $event);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $this->agendaManager->updateEvent($event);

            return new JsonResponse($event, 200);
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_agenda_update', array('event' => $event->getId())
            ),
            'event' => $event
        );
    }

    /**
     * @EXT\Route(
     *     "/{event}/delete",
     *     name="claro_agenda_delete_event",
     *     options = {"expose"=true}
     * )
     *
     * @param Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Event $event)
    {
        $this->checkPermission($event);
        $removed = $this->agendaManager->deleteEvent($event);

        return new JsonResponse($removed, 200);
    }

    /**
     * @EXT\Route(
     *     "/resize/event/{event}/day/{day}/minute/{minute}",
     *     name="claro_workspace_agenda_resize",
     *     options = {"expose"=true}
     * )
     */
    public function resizeAction(Event $event, $day, $minute)
    {
        $this->checkPermission($event);
        $data = $this->agendaManager->updateEndDate($event, $day, $minute);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/move/event/{event}/day/{day}/minute/{minute}",
     *     name="claro_workspace_agenda_move",
     *     options = {"expose"=true}
     * )
     */
    public function moveAction(Event $event, $day, $minute)
    {
        $this->checkPermission($event);
        $data = $this->agendaManager->moveEvent($event, $day, $minute);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/export",
     *     name="claro_workspace_agenda_export"
     * )
     * @param Workspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportWorkspaceEventIcsAction(Workspace $workspace)
    {
        //if you can open the tool, you can export
        if (!$this->security->isGranted('agenda', $workspace)) {
            throw new AccessDeniedException("The event cannot be updated");
        }

        return $this->exportEvent($workspace);
    }

    /**
     * @EXT\Route(
     *     "/desktop/export",
     *     name="claro_desktop_agenda_export"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportDesktopEventIcsAction()
    {
        return $this->exportEvent();
    }

    private function exportEvent($workspace = null)
    {
        $file = $this->agendaManager->export();
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $name = $workspace ? $workspace->getName(): 'desktop';
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $name . '.ics');
        $response->headers->set('Content-Type', ' text/calendar');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    private function checkPermission(Event $event)
    {
        if (!$this->security->isGranted('EDIT', $event)) {
            throw new AccessDeniedException("The event cannot be updated");
        }
    }
}
