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

use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ToolsController extends Controller
{
    private $request;
    private $toolManager;
    private $formFactory;
    private $router;
    private $sc;
    private $desktopToolAdmin;

    /**
     * @DI\InjectParams({
     *     "request"     = @DI\Inject("request"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "router"      = @DI\Inject("router"),
     *     "sc"          = @DI\Inject("security.context")
     * })
     */
    public function __construct
    (
        Request $request,
        ToolManager $toolManager,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        SecurityContextInterface $sc
    )
    {
        $this->request          = $request;
        $this->toolManager      = $toolManager;
        $this->formFactory      = $formFactory;
        $this->router           = $router;
        $this->sc               = $sc;
        $this->desktopToolAdmin = $toolManager->getAdminToolByName('desktop_tools');
    }

    /**
     * @EXT\Route(
     *     "/show",
     *     name="claro_admin_tool_show"
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Tool:desktopToolNames.html.twig")
     *
     * change the desktop tool name.
     *
     * @return Response
     */
    public function showToolAction()
    {
        $this->checkOpen();

        $forms = array();
        $tools = $this->toolManager->getAllTools();

        foreach ($tools as $i => $tool) {
            $forms[] = $this->formFactory->create(FormFactory::TYPE_TOOL, array(), $tool)->createView();
        }

        return array(
            'forms' => $forms,
            'tools' => $tools
        );
    }

     /**
     * @EXT\Route(
     *     "/modify/{id}",
     *     name="claro_admin_tool_modify"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "tool",
     *      class="ClarolineCoreBundle:Tool\Tool",
     *      options={"id" = "id", "strictId" = true}
     * )
     * change the desktop tool name.
     *
     * @param Tool $tool
     *
     * @return Response
     */
    public function modifyToolAction(Tool $tool)
    {
        $this->checkOpen();

        $form = $this->formFactory->create(FormFactory::TYPE_TOOL, array(), $tool);

        if ($this->request->getMethod() === 'POST') {
            $form->handleRequest($this->request);
            if ($form->isValid()) {
                $this->toolManager->editTool($tool);
            }
        }

        return new RedirectResponse($this->router->generate('claro_admin_tool_show'));
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->desktopToolAdmin)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
