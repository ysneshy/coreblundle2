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

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\ToolManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use \Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdministrationController extends Controller
{
    private $eventDispatcher;
    private $toolManager;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"              = @DI\Inject("security.context"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        ToolManager $toolManager,
        SecurityContextInterface $sc,
        $container
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->toolManager = $toolManager;
        $this->sc = $sc;
        $this->container = $container;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_index"
     * )
     *
     * Displays the administration section index.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->sc->getToken()->getRoles());

        if (count($tools) === 0) {
            throw new AccessDeniedException();
        }

        return $this->redirect($this->generateUrl('claro_admin_open_tool', array('toolName' => $tools[0]->getName())));
    }

    /**
     * @EXT\Route(
     *    "/open/{toolName}",
     *    name="claro_admin_open_tool",
     *    options = {"expose"=true}
     * )
     *
     * @param $toolName
     *
     * @return Response
     */
    public function openAdministrationToolAction($toolName)
    {
        $event = $this->eventDispatcher->dispatch(
            'administration_tool_' . $toolName,
            'OpenAdministrationTool',
            array('toolName' => $toolName)
        );

        return $event->getResponse();
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Administration:left_bar.html.twig")
     * @return array
     */
    public function renderLeftBarAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->sc->getToken()->getRoles());

        return array('tools' => $tools);
    }
}
