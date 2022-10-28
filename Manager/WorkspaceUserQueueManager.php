<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_user_queue_manager")
 */
class WorkspaceUserQueueManager 
{
    private $dispatcher;
    private $objectManager;
    private $pagerFactory;
    private $roleManager;
    private $wksQrepo;
    private $workspaceManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "dispatcher"       = @DI\Inject("claroline.event.event_dispatcher"),
     *     "objectManager"    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"     = @DI\Inject("claroline.pager.pager_factory"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $objectManager,
        PagerFactory $pagerFactory,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager
    )
    {
        $this->dispatcher = $dispatcher;
        $this->objectManager = $objectManager;
        $this->pagerFactory = $pagerFactory;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->wksQrepo = $this->objectManager->getRepository('ClarolineCoreBundle:Workspace\WorkspaceRegistrationQueue');
    }

    public function getAll(Workspace $workspace, $page = 1,$max = 20)
    {
        $query = $this->wksQrepo->findByWorkspace($workspace);

        return $this->pagerFactory->createPagerFromArray($query, $page, $max);
    }

    public function validateRegistration(WorkspaceRegistrationQueue $wksqrq)
    {
        $this->roleManager->associateRolesToSubjects(
            array($wksqrq->getUser()),
            array($wksqrq->getRole()),
            true
        );
        $this->objectManager->remove($wksqrq);
        $this->objectManager->flush();
    }

    public function removeRegistrationQueue(WorkspaceRegistrationQueue $wksqrq)
    {
        $this->dispatcher->dispatch(
            'log',
            'Log\LogWorkspaceRegistrationDecline',
            array($wksqrq)
        );

        $this->objectManager->remove($wksqrq);
        $this->objectManager->flush();
    }

    public function removeUserFromWorkspaceQueue(Workspace $workspace, User $user)
    {
        $queue = $this->wksQrepo->findOneByWorkspaceAndUser($workspace, $user);

        if (!is_null($queue)) {
            $this->objectManager->remove($queue);
            $this->objectManager->flush();
        }
    }
}