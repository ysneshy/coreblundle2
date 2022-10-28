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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\AgendaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\RouterInterface;

/**
 * Controller of the Agenda
 */
class DesktopAgendaController extends Controller
{
    private $security;
    private $formFactory;
    private $om;
    private $request;
    private $translator;
    private $agendaManager;
    private $router;

    /**
     * @DI\InjectParams({
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "translator"         = @DI\Inject("translator"),
     *     "agendaManager"      = @DI\Inject("claroline.manager.agenda_manager"),
     *     "router"             = @DI\Inject("router")
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        Translator $translator,
        AgendaManager $agendaManager,
        RouterInterface $router
    )
    {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->translator = $translator;
        $this->agendaManager = $agendaManager;
        $this->router = $router;
    }
    /**
     * @Route(
     *     "/show",
     *     name="claro_desktop_agenda_show",
     *     options = {"expose"=true}
     * )
     */
    public function desktopShowAction()
    {
        $data = $this->agendaManager->desktopEvents($this->get('security.context')->getToken()->getUser());

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/add/event/form",
     *     name="claro_desktop_agenda_add_event_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Agenda:addEventModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function addEventModalFormAction()
    {
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_desktop_agenda_add')
        );
    }

    /**
     * @Route(
     *     "/add",
     *     name="claro_desktop_agenda_add"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Agenda:addEventModalForm.html.twig")
    */
    public function addEvent()
    {
        $formType = $this->get('claroline.form.agenda');
        $form = $this->createForm($formType, new Event());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $event = $form->getData();
            $data = $this->agendaManager->addEvent($event, null);

            return new JsonResponse(array($data), 200);
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_desktop_agenda_add_event_form', array())
        );
    }

    /**
     * @EXT\Route(
     *     "/tasks",
     *     name="claro_desktop_agenda_tasks"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Agenda:tasks.html.twig")
     */
    public function tasksAction()
    {
        $usr = $this->get('security.context')->getToken()->getUser();
        $events = $this->agendaManager->desktopEvents($usr, true);

        return array('events' => $events);
    }

    /**
     * @EXT\Route(
     *     "/widget/{order}",
     *     name="claro_desktop_agenda_widget"
     * )
     * @EXT\Template("ClarolineCoreBundle:Widget:agenda_widget.html.twig")
     */
    public function widgetAction($order = null)
    {
        $em = $this-> get('doctrine.orm.entity_manager');
        $usr = $this->get('security.context')->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineCoreBundle:Event')->findDesktop($usr, false);
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByUserWithoutAllDay($usr, 5, $order);

        return array('listEvents' => array_merge($listEvents, $listEventsDesktop));
    }


    /**
     * @EXT\Route(
     *     "/import/modal/form",
     *     name="claro_agenda_import_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\agenda:importIcsModalForm.html.twig")
     * @return array
     */
    public function importEventsModalForm()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/import",
     *     name="claro_agenda_import"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\agenda:importIcsModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function importsEventsIcsAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_AGENDA_IMPORTER);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $events = $this->agendaManager->importEvents($form->get('file')->getData());

            return new JsonResponse($events, 200);
        }

        return array('form' => $form->createView());
    }
}
