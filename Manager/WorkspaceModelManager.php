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

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\Model\ResourceModel;
use Claroline\CoreBundle\Event\NotPopulatedEventException;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_model_manager")
 */
class WorkspaceModelManager
{
    private $dispatcher;
    private $homeTabManager;
    private $om;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $toolManager;
    private $toolRightsManager;
    private $sc;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "dispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "homeTabManager"    = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "sc"                = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        StrictDispatcher         $dispatcher,
        HomeTabManager           $homeTabManager,
        ObjectManager            $om,
        ResourceManager          $resourceManager,
        RightsManager            $rightsManager,
        RoleManager              $roleManager,
        ToolManager              $toolManager,
        ToolRightsManager        $toolRightsManager,
        SecurityContextInterface $sc
    )
    {
        $this->dispatcher        = $dispatcher;
        $this->homeTabManager    = $homeTabManager;
        $this->om                = $om;
        $this->resourceManager   = $resourceManager;
        $this->rightsManager     = $rightsManager;
        $this->roleManager       = $roleManager;
        $this->toolManager       = $toolManager;
        $this->toolRightsManager = $toolRightsManager;
        $this->modelRepository   = $this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel');
        $this->sc                = $sc;
    }

    /**
     * @param $name
     * @param Workspace $workspace
     * @return WorkspaceModel
     */
    public function create($name, Workspace $workspace)
    {
        $model = new WorkspaceModel();
        $model->setName($name);
        $model->setWorkspace($workspace);

        if ($this->sc->getToken()->getUser() !== 'anon.') {
            $model->addUser($this->sc->getToken()->getUser());
        }
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    /**
     * @param WorkspaceModel $model
     * @param $name
     * @return WorkspaceModel
     */
    public function edit(WorkspaceModel $model, $name)
    {
        $model->setName($name);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    /**
     * @param WorkspaceModel $model
     */
    public function delete(WorkspaceModel $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    /**
     * @param Workspace $workspace
     * @return mixed
     */
    public function getByWorkspace(Workspace $workspace)
    {
        return $this->modelRepository->findByWorkspace($workspace);
    }

    /**
     * @param WorkspaceModel $model
     * @param User $user
     */
    public function addUser(WorkspaceModel $model, User $user)
    {
        $model->addUser($user);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param Group $group
     */
    public function addGroup(WorkspaceModel $model, Group $group)
    {
        $model->addGroup($group);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param Group $group
     */
    public function removeGroup(WorkspaceModel $model, Group $group)
    {
        $group->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param User $user
     */
    public function removeUser(WorkspaceModel $model, User $user)
    {
        $user->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $users
     */
    public function addUsers(WorkspaceModel $model, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->addUser($model, $user);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $groups
     */
    public function addGroups(WorkspaceModel $model, array $groups)
    {
        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->addGroup($model, $group);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $resourceNodes
     * @param $isCopy
     * @return array
     */
    public function addResourceNodes(WorkspaceModel $model, array $resourceNodes, $isCopy)
    {
        $this->om->startFlushSuite();
        $resourceModels = [];

        foreach ($resourceNodes as $resourceNode) {
            $resourceModels[] = $this->addResourceNode($model, $resourceNode, $isCopy);
        }

        $this->om->endFlushSuite();

        return $resourceModels;
    }

    /**
     * @param WorkspaceModel $model
     * @param ResourceNode $resourceNode
     * @param $isCopy
     * @return ResourceModel
     */
    public function addResourceNode(WorkspaceModel $model, ResourceNode $resourceNode, $isCopy)
    {
        $resourceModel = new ResourceModel();
        $resourceModel->setModel($model);
        $resourceModel->setResourceNode($resourceNode);
        $resourceModel->setIsCopy($isCopy);
        $this->om->persist($resourceModel);
        $this->om->flush();

        return $resourceModel;
    }

    /**
     * @param ResourceModel $resourceModel
     */
    public function removeResourceModel(ResourceModel $resourceModel)
    {
        $this->om->remove($resourceModel);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $homeTabs
     */
    public function addHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();

        foreach ($homeTabs as $homeTab) {
            $this->addHomeTab($model, $homeTab);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param HomeTab $homeTab
     */
    public function addHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->addHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param HomeTab $homeTab
     */
    public function removeHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->removeHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $homeTabs
     */
    public function updateHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();
        $oldHomeTabs = $model->getHomeTabs();

        foreach ($oldHomeTabs as $oldHomeTab) {
            $search = array_search($oldHomeTab, $homeTabs, true);

            if ($search !== false) {
                unset($homeTabs[$search]);
            } else {
                $this->removeHomeTab($model, $oldHomeTab);
            }
        }
        
        $this->addHomeTabs($model, $homeTabs);
        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @return array
     */
    public function toArray(WorkspaceModel $model)
    {
        $array = [];
        $array['name'] = $model->getName();

        return $array;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateWorkspaceRoles(
        Workspace $source,
        Workspace $workspace,
        User $user
    )
    {
        $guid = $workspace->getGuid();
        $roles = $source->getRoles();
        $unusedRolePartName = '_' . $source->getGuid();

        foreach ($roles as $role) {
            $roleName = str_replace($unusedRolePartName, '', $role->getName());

            $createdRole = $this->roleManager->createWorkspaceRole(
                $roleName . '_' . $guid,
                $role->getTranslationKey(),
                $workspace,
                $role->isReadOnly()
            );



            if ($roleName === 'ROLE_WS_MANAGER') {
                $user->addRole($createdRole);
                $this->om->persist($user);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    private function duplicateOrderedTools(Workspace $source, Workspace $workspace)
    {
        $orderedTools = $source->getOrderedTools();
        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);

        foreach ($orderedTools as $orderedTool) {
            $workspaceOrderedTool = $this->toolManager->addWorkspaceTool(
                $orderedTool->getTool(),
                $orderedTool->getOrder(),
                $orderedTool->getName(),
                $workspace
            );

            $rights = $orderedTool->getRights();

            foreach ($rights as $right) {
                $role = $right->getRole();

                if ($role->getType() === 1) {
                    $this->toolRightsManager->createToolRights(
                        $workspaceOrderedTool,
                        $role,
                        $right->getMask()
                    );
                } else {
                    $key = $role->getTranslationKey();

                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $this->toolRightsManager->createToolRights(
                            $workspaceOrderedTool,
                            $workspaceRoles[$key],
                            $right->getMask()
                        );
                    }
                }
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateRootDirectory(
        Workspace $source,
        Workspace $workspace,
        User $user
    )
    {
        $rootDirectory = new Directory();
        $rootDirectory->setName($workspace->getName());
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $resource = $this->resourceManager->create(
            $rootDirectory,
            $directoryType,
            $user,
            $workspace,
            null,
            null,
            array()
        );

        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);
        $root = $this->resourceManager->getWorkspaceRoot($source);
        $rights = $root->getRights();

        foreach ($rights as $right) {
            $role = $right->getRole();

            if ($role->getType() === 1) {
                $newRight = $this->rightsManager->getRightsFromIdentityMapOrScheduledForInsert(
                    $role->getName(),
                    $resource->getResourceNode()
                );
            } else {
                $newRight = new ResourceRights();
                $newRight->setResourceNode($resource->getResourceNode());

                if ($role->getWorkspace() === $source) {
                    $key = $role->getTranslationKey();

                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $newRight->setRole($workspaceRoles[$key]);
                    }
                } else {
                    $newRight->setRole($role);
                }
            }

            $newRight->setMask($right->getMask());
            $newRight->setCreatableResourceTypes($right->getCreatableResourceTypes()->toArray());
            $this->om->persist($newRight);
        }
        $this->om->flush();

        return $resource;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param array $homeTabs
     */
    private function duplicateHomeTabs(
        Workspace $source,
        Workspace $workspace,
        array $homeTabs
    )
    {
        $this->om->startFlushSuite();

        $homeTabConfigs = $this->homeTabManager
            ->getHomeTabConfigsByWorkspaceAndHomeTabs($source, $homeTabs);
        $order = 1;
        $widgetCongigErrors = array();

        foreach ($homeTabConfigs as $homeTabConfig) {
            $homeTab = $homeTabConfig->getHomeTab();
            $widgetHomeTabConfigs = $homeTab->getWidgetHomeTabConfigs();

            $newHomeTab = new HomeTab();
            $newHomeTab->setType('workspace');
            $newHomeTab->setWorkspace($workspace);
            $newHomeTab->setName($homeTab->getName());
            $this->om->persist($newHomeTab);

            $newHomeTabConfig = new HomeTabConfig();
            $newHomeTabConfig->setHomeTab($newHomeTab);
            $newHomeTabConfig->setWorkspace($workspace);
            $newHomeTabConfig->setType('workspace');
            $newHomeTabConfig->setLocked($homeTabConfig->isVisible());
            $newHomeTabConfig->setLocked($homeTabConfig->isLocked());
            $newHomeTabConfig->setTabOrder($order);
            $this->om->persist($newHomeTabConfig);
            $order++;

            foreach ($widgetHomeTabConfigs as $widgetConfig) {
                $widgetInstance = $widgetConfig->getWidgetInstance();
                $widget = $widgetInstance->getWidget();

                $newWidgetInstance = new WidgetInstance();
                $newWidgetInstance->setIsAdmin(false);
                $newWidgetInstance->setIsDesktop(false);
                $newWidgetInstance->setWorkspace($workspace);
                $newWidgetInstance->setWidget($widget);
                $newWidgetInstance->setName($widgetInstance->getName());
                $this->om->persist($newWidgetInstance);

                $newWidgetConfig = new WidgetHomeTabConfig();
                $newWidgetConfig->setType('workspace');
                $newWidgetConfig->setWorkspace($workspace);
                $newWidgetConfig->setHomeTab($newHomeTab);
                $newWidgetConfig->setWidgetInstance($newWidgetInstance);
                $newWidgetConfig->setVisible($widgetConfig->isVisible());
                $newWidgetConfig->setLocked($widgetConfig->isLocked());
                $newWidgetConfig->setWidgetOrder($widgetConfig->getWidgetOrder());
                $this->om->persist($newWidgetConfig);

                if ($widget->isConfigurable()) {

                    try {
                        $this->dispatcher->dispatch(
                            'copy_widget_config_' . $widget->getName(),
                            'CopyWidgetConfiguration',
                            array($widgetInstance, $newWidgetInstance)
                        );
                    } catch (NotPopulatedEventException $e) {
                        $widgetCongigErrors[] = array(
                            'widgetName' => $widget->getName(),
                            'widgetInstanceName' => $widgetInstance->getName(),
                            'error' => $e->getMessage()
                        );
                    }
                }
            }
        }
        $this->om->endFlushSuite();

        return $widgetCongigErrors;
    }

    /**
     * @param array $resourcesModels
     * @param \Claroline\CoreBundle\Entity\Resource\Directory $rootDirectory
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateResources(
        array $resourcesModels,
        Directory $rootDirectory,
        Workspace $workspace,
        User $user
    )
    {
        $this->om->startFlushSuite();

        $copies = array();
        $resourcesErrors = array();
        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);

        foreach ($resourcesModels as $resourceModel) {
            $resourceNode = $resourceModel->getResourceNode();

            if ($resourceModel->isCopy()) {

                try {
                    $copy = $this->resourceManager->copy(
                        $resourceNode,
                        $rootDirectory->getResourceNode(),
                        $user,
                        false,
                        false
                    );
                    $copies[] = $copy;
                } catch (NotPopulatedEventException $e) {
                    $resourcesErrors[] = array(
                        'resourceName' => $resourceNode->getName(),
                        'resourceType' => $resourceNode->getResourceType()->getName(),
                        'type' => 'copy',
                        'error' => $e->getMessage()
                    );
                    continue;
                }

                /*** Copies rights ***/
                $this->duplicateRights(
                    $resourceNode,
                    $copy->getResourceNode(),
                    $workspaceRoles
                );

                /*** Copies content of a directory ***/
                if ($resourceNode->getResourceType()->getName() === 'directory') {
                    $errors = $this->duplicateDirectoryContent(
                        $resourceNode,
                        $copy->getResourceNode(),
                        $user,
                        $workspaceRoles
                    );
                    $resourcesErrors = array_merge_recursive($resourcesErrors, $errors);
                }
            } else {
                $shortcut = $this->resourceManager->makeShortcut(
                    $resourceNode,
                    $rootDirectory->getResourceNode(),
                    $user,
                    new ResourceShortcut()
                );
                $copies[] = $shortcut;
            }
        }

        /*** Sets previous and next for each copied resource ***/
        $this->linkResourcesArray($copies);

        $this->om->endFlushSuite();

        return $resourcesErrors;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    private function getArrayRolesByWorkspace(Workspace $workspace)
    {
        $workspaceRoles = array();
        $uow = $this->om->getUnitOfWork();
        $wRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $scheduledForInsert = $uow->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if (get_class($entity) === 'Claroline\CoreBundle\Entity\Role') {
                if ($entity->getWorkspace()) {
                    if ($entity->getWorkspace()->getGuid() === $workspace->getGuid()) {
                        $wRoles[] = $entity;
                    }
                }
            }
        }

        //now we build the array
        foreach ($wRoles as $wRole) {
            $workspaceRoles[$wRole->getTranslationKey()] = $wRole;
        }

        return $workspaceRoles;
    }

    /**
     * @param array $resources
     */
    private function linkResourcesArray(array $resources)
    {
        for ($i = 0; $i < count($resources); $i++) {

            if (isset($resources[$i]) && isset($resources[$i + 1])) {
                $node = $resources[$i]->getResourceNode();
                $nextNode = $resources[$i + 1]->getResourceNode();
                $node->setNext($nextNode);
                $nextNode->setPrevious($node);
                $this->om->persist($node);
                $this->om->persist($nextNode);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $copy
     * @param array $workspaceRoles
     */
    private function duplicateRights(
        ResourceNode $resourceNode,
        ResourceNode $copy,
        array $workspaceRoles
    )
    {
        $rights = $resourceNode->getRights();
        $workspace = $resourceNode->getWorkspace();

        foreach ($rights as $right) {
            $role = $right->getRole();
            $key = $role->getTranslationKey();

            $newRight = new ResourceRights();
            $newRight->setResourceNode($copy);
            $newRight->setMask($right->getMask());
            $newRight->setCreatableResourceTypes(
                $right->getCreatableResourceTypes()->toArray()
            );

            if ($role->getWorkspace() === $workspace &&
                isset($workspaceRoles[$key]) &&
                !empty($workspaceRoles[$key])) {

                $newRight->setRole($workspaceRoles[$key]);
            } else {
                $newRight->setRole($role);
            }
            $this->om->persist($newRight);
        }
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $directory
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $directoryCopy
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param array $workspaceRoles
     */
    private function duplicateDirectoryContent(
        ResourceNode $directory,
        ResourceNode $directoryCopy,
        User $user,
        array $workspaceRoles
    )
    {
        $children = $directory->getChildren();
        $copies = array();
        $resourcesErrors = array();

        foreach ($children as $child) {
           try {
                $copy = $this->resourceManager->copy(
                    $child,
                    $directoryCopy,
                    $user,
                    false,
                    false
                );
                $copies[] = $copy;
            } catch (NotPopulatedEventException $e) {
                $resourcesErrors[] = array(
                    'resourceName' => $child->getName(),
                    'resourceType' => $child->getResourceType()->getName(),
                    'type' => 'copy',
                    'error' => $e->getMessage()
                );
                continue;
            }

            /*** Copies rights ***/
            $this->duplicateRights(
                $child,
                $copy->getResourceNode(),
                $workspaceRoles
            );

            /*** Recursive call for a directory ***/
            if ($child->getResourceType()->getName() === 'directory') {
                $errors = $this->duplicateDirectoryContent(
                    $child,
                    $copy->getResourceNode(),
                    $user,
                    $workspaceRoles
                );
                $resourcesErrors = array_merge_recursive($resourcesErrors, $errors);
            }
        }

        $this->linkResourcesArray($copies);
        $this->om->flush();

        return $resourcesErrors;
    }

    public function addDataFromModel(WorkspaceModel $model, Workspace $workspace, User $user, &$errors)
    {
        $modelWorkspace = $model->getWorkspace();
        $resourcesModels = $model->getResourcesModel();
        $homeTabs = $model->getHomeTabs();

        $this->duplicateWorkspaceRoles($modelWorkspace, $workspace, $user);
        $this->duplicateOrderedTools($modelWorkspace, $workspace);
        $rootDirectory = $this->duplicateRootDirectory($modelWorkspace, $workspace, $user);
        $errors['widgetConfigErrors'] = $this->duplicateHomeTabs($modelWorkspace, $workspace, $homeTabs->toArray());
        $errors['resourceErrors'] = $this->duplicateResources(
            $resourcesModels->toArray(),
            $rootDirectory,
            $workspace,
            $user
        );
    }
}
