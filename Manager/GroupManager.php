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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.group_manager")
 */
class GroupManager
{
    private $om;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    private $pagerFactory;
    private $translator;
    private $eventDispatcher;
    private $roleManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "translator"      = @DI\Inject("translator"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        Translator $translator,
        StrictDispatcher $eventDispatcher,
        RoleManager $roleManager
    )
    {
        $this->om = $om;
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;
    }

    /**
     * Persists and flush a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function insertGroup(Group $group)
    {
        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Removes a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function deleteGroup(Group $group)
    {
        $this->om->remove($group);
        $this->om->flush();
    }

    /**
     * @todo what does this method do ?
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param Role[] $oldRoles
     */
    public function updateGroup(Group $group, array $oldRoles)
    {
        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($group);
        $newRoles = $group->getPlatformRoles();
        $oldRolesTranslationKeys = array();

        foreach ($oldRoles as $oldRole) {
            $oldRolesTranslationKeys[] = $oldRole->getTranslationKey();
        }

        $newRolesTransactionKey = array();

        foreach ($newRoles as $newRole) {
            $newRolesTransactionKeys[] = $newRole->getTranslationKey();
        }

        $changeSet['platformRole'] = array($oldRolesTranslationKeys, $newRolesTransactionKey);
        $this->eventDispatcher->dispatch('log', 'Log\LogGroupUpdate', array($group, $changeSet));

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Adds an array of user to a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function addUsersToGroup(Group $group, array $users)
    {
        if(!$this->validateAddUsersToGroup($users, $group)) {
            throw new Exception\AddRoleException();
        }

        foreach ($users as $user) {
            if (!$group->containsUser($user)) {
                $group->addUser($user);
                $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', array($group, $user));
            }
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Removes all users from a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     */
    public function removeAllUsersFromGroup(Group $group)
    {
        $users = $group->getUsers();

        foreach ($users as $user) {
            $group->removeUser($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     * Removes an array of user from a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param User[]                             $users
     */
    public function removeUsersFromGroup(Group $group, array $users)
    {
        foreach ($users as $user) {
            $group->removeUser($user);
        }

        $this->om->persist($group);
        $this->om->flush();
    }

    /**
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param array                              $users
     *
     * @return array
     */
    public function importUsers(Group $group, array $users)
    {
        $toImport = $this->userRepo->findByUsernames($users);
        $this->addUsersToGroup($group, $toImport);
    }

    /**
     * Serialize a group array.
     *
     * @param Group[] $groups
     *
     * @return array
     */
    public function convertGroupsToArray(array $groups)
    {
        $content = array();
        $i = 0;

        foreach ($groups as $group) {
            $content[$i]['id'] = $group->getId();
            $content[$i]['name'] = $group->getName();

            $rolesString = '';
            $roles = $group->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;

            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), array(), 'platform')}";

                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                $j++;
            }
            $content[$i]['roles'] = $rolesString;
            $i++;
        }

        return $content;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $page
     * @param integer $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsiders(Workspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsiders($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string $search
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspaceOutsidersByName(Workspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findWorkspaceOutsidersByName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspace(Workspace $workspace, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspace($workspace, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     *
     * @return Group[]
     */
    public function getGroupsByWorkspaces(array $workspaces)
    {
        return $this->groupRepo->findGroupsByWorkspaces($workspaces, true);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     * @param string                                                     $search
     *
     * @return Group[]
     */
    public function getGroupsByWorkspacesAndSearch(array $workspaces, $search)
    {
        return $this->groupRepo->findGroupsByWorkspacesAndSearch(
            $workspaces,
            $search
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string $search
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByWorkspaceAndName(Workspace $workspace, $search, $page, $max = 50)
    {
        $query = $this->groupRepo->findByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroups($page, $max = 50, $orderedBy = 'id', $order = null)
    {
        $query = $this->groupRepo->findAll(false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByName($search, $page, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param integer $page
     * @param int $max
     * @param string $orderedBy
     * @param null $order
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRoles(array $roles, $page = 1, $max = 50, $orderedBy = 'id', $order = null)
    {
        $query = $this->groupRepo->findByRoles($roles, true, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRoles(array $roles, Workspace $workspace, $page = 1, $max = 50)
    {
        $query = $this->groupRepo->findOutsidersByWorkspaceRoles($roles, $workspace, true);

        return  $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param string $name
     * @param integer $page
     * @param int $max
     * @param string $orderedBy
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getGroupsByRolesAndName(array $roles, $name, $page = 1, $max = 50, $orderedBy = 'id')
    {
        $query = $this->groupRepo->findByRolesAndName($roles, $name, true, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     * @param string                                                   $name
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOutsidersByWorkspaceRolesAndName(
        array $roles,
        $name,
        Workspace $workspace,
        $page = 1,
        $max = 50
    )
    {
        $query = $this->groupRepo->findOutsidersByWorkspaceRolesAndName($roles, $name, $workspace, true);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroups($page, $max = 50)
    {
        $query = $this->groupRepo->findAll(false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param integer $page
     * @param string $search
     * @param int $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getAllGroupsBySearch($page, $search, $max = 50)
    {
        $query = $this->groupRepo->findAllGroupsBySearch($search);

        return $this->pagerFactory->createPagerFromArray($query, $page, $max);
    }

    /**
     * @param string[] $names
     *
     * @return Group[]
     */
    public function getGroupsByNames(array $names)
    {
        if (count($names) > 0) {
            return $this->groupRepo->findGroupsByNames($names);
        }

        return array();
    }

    /**
     * Returns users who don't have access to the model $model
     *
     * @param WorkspaceModel $model
     */
    public function getUsersNotSharingModel(WorkspaceModel $model, $page = 1, $max = 20)
    {
        $res = $this->groupRepo->findGroupsNotSharingModel($model, false);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * Returns users who don't have access to the model $model
     *
     * @param WorkspaceModel $model
     */
    public function getUsersNotSharingModelBySearch(WorkspaceModel $model, $page = 1, $search, $max = 20)
    {
        $res = $this->groupRepo->findGroupsNotSharingModelBySearch($model, $search, false);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * Sets an array of platform role to a group.
     *
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param array                              $roles
     */
    public function setPlatformRoles(Group $group, $roles)
    {
        foreach ($group->getPlatformRoles() as $role) {
            $group->removeRole($role);
        }

        $this->om->persist($group);
        $this->roleManager->associateRoles($group, $roles);
        $this->om->flush();
    }

    public function validateAddUsersToGroup(array $users, Group $group)
    {
        $countToRegister = count($users);
        $roles = $group->getPlatformRoles();

        foreach ($roles as $role) {
            $max = $role->getMaxUsers();
            $countRegistered = $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);

            if ($max < $countRegistered + $countToRegister) {
                return false;
            }
        }

        return true;
    }

    public function getGroupByName($name, $executeQuery = true)
    {
        return $this->groupRepo->findGroupByName($name, $executeQuery);
    }
}
