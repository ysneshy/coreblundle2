<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\ExportDirectoryTemplateEvent;
use Claroline\CoreBundle\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * @DI\Service
 */
class DirectoryListener
{
    private $container;
    private $roleManager;
    private $resourceManager;
    private $rightsManager;
    private $maskManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"        = @DI\Inject("security.context"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "templating"      = @DI\Inject("templating"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        MaskManager $maskManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        FormFactoryInterface $formFactory,
        TwigEngine $templating,
        ContainerInterface $container
    )
    {
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->container = $container;
        $this->maskManager = $maskManager;
    }

    /**
     * @DI\Observe("create_form_directory")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new DirectoryType, new Directory());
        $response = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_directory")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $event->setResources(array($form->getData()));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_directory")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $dir = $event->getResourceNode();
        $file = $this->resourceManager->download(array($dir));
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_directory")
     *
     * @param DeleteResourceEvent $event
     *
     * Removes a directory.
     */
    public function delete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_directory")
     *
     * @param CopyResourceEvent $event
     *
     * Copy a directory.
     */
    public function copy(CopyResourceEvent $event)
    {
        $resourceCopy = new Directory();
        $event->setCopy($resourceCopy);
    }
}
