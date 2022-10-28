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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    /** @var RoleRepository */
    private $roleRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var GroupRepository */
    private $groupRepo;
    private $dispatcher;
    private $om;
    private $messageManager;
    private $container;
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "messageManager" = @DI\Inject("claroline.manager.message_manager"),
     *     "container"      = @DI\Inject("service_container"),
     *     "translator"     = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        MessageManager $messageManager,
        Container $container,
        Translator $translator
    )
    {
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->messageManager = $messageManager;
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * @param string                                                   $name
     * @param string                                                   $translationKey
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param boolean                                                  $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createWorkspaceRole($name, $translationKey, Workspace $workspace, $isReadOnly = false)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::WS_ROLE);
        $role->setWorkspace($workspace);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    /**
     * @param string  $name
     * @param string  $translationKey
     * @param boolean $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createBaseRole($name, $translationKey, $isReadOnly = true)
    {
        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    /**
     * @param string  $name
     * @param string  $translationKey
     * @param boolean $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createCustomRole($name, $translationKey, $isReadOnly = false)
    {
        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::CUSTOM_ROLE);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    /**
     * @param User $user
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createUserRole(User $user)
    {
        $username = $user->getUsername();
        $roleName = 'ROLE_USER_' . strtoupper($username);

        $this->om->startFlushSuite();

        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($roleName);
        $role->setTranslationKey($username);
        $role->setReadOnly(true);
        $role->setType(Role::USER_ROLE);
        $this->om->persist($role);
        $this->associateRole($user, $role);

        $this->om->endFlushSuite();

        return $role;
    }

    /**
     * @param string $username
     */
    public function renameUserRole(Role $role, $username)
    {
        $roleName = 'ROLE_USER_' . strtoupper($username);
        $role->setName($roleName);
        $role->setTranslationKey($username);
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param string $roleName
     * @throws \Exception
     */
    public function setRoleToRoleSubject(AbstractRoleSubject $ars, $roleName)
    {
        $role = $this->roleRepo->findOneBy(array('name' => $roleName));
        $validated = $this->validateRoleInsert($ars, $role);

        if (!$validated) {
            throw new Exception\AddRoleException();
        }

        if (get_class($ars) === 'Claroline\CoreBundle\Entity\Group' && $role->getName() === 'ROLE_USER') {
            throw new Exception\AddRoleException('ROLE_USER cannot be added to groups');
        }

        if (!is_null($role)) {
            $ars->addRole($role);
            $this->om->persist($ars);
            $this->om->flush();
        }
    }

    /**
     * @param integer $roleId
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRole($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param boolean $sendMail
     *
     * @throws Exception\AddRoleException
     */
    public function associateRole(AbstractRoleSubject $ars, Role $role, $sendMail = false)
    {
        if (!$this->validateRoleInsert($ars, $role)) {
            throw new Exception\AddRoleException('Role cannot be added');
        }

        if (get_class($ars) === 'Claroline\CoreBundle\Entity\Group' && $role->getName() === 'ROLE_USER') {
            throw new Exception\AddRoleException('ROLE_USER cannot be added to groups');
        }

        if (!$ars->hasRole($role->getName())) {

            $ars->addRole($role);
            $this->om->startFlushSuite();

            $this->dispatcher->dispatch(
                'log',
                'Log\LogRoleSubscribe',
                array($role, $ars)
            );
            $this->om->persist($ars);
            $this->om->endFlushSuite();

            if ($sendMail) {
                $this->sendInscriptionMessage($ars, $role);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param \Claroline\CoreBundle\Entity\Role                $role
     */
    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        if ($ars->hasRole($role->getName())) {
            $ars->removeRole($role);
            $this->om->startFlushSuite();

            $this->dispatcher->dispatch(
                'log',
                'Log\LogRoleUnsubscribe',
                array($role, $ars)
            );

            $this->om->persist($ars);
            $this->om->endFlushSuite();
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param array     $roles
     * @param boolean                                          $sendMail
     */
    public function associateRoles(AbstractRoleSubject $ars, $roles, $sendMail = false)
    {
        foreach ($roles as $role) {
            $this->associateRole($ars, $role, $sendMail);
        }
        $this->om->persist($ars);
        $this->om->flush();
    }


    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject[]
     * @param \Claroline\CoreBundle\Entity\Role $role
     */
    public function associateRoleToMultipleSubjects(array $subjects, Role $role)
    {
        foreach ($subjects as $subject) {
            $this->associateRole($subject, $role);
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function resetRoles(User $user)
    {
        $userRole = $this->roleRepo->findOneByName('ROLE_USER');
        $roles = $this->roleRepo->findPlatformRoles($user);

        foreach ($roles as $role) {
            if ($role !== $userRole) {
                $user->removeRole($role);
            }
        }
        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject         $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role                        $role
     */
    public function dissociateWorkspaceRole(AbstractRoleSubject $subject, Workspace $workspace, Role $role)
    {
        $this->checkWorkspaceRoleEditionIsValid(array($subject), $workspace, array($role));
        $this->dissociateRole($subject, $role);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject         $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function resetWorkspaceRolesForSubject(AbstractRoleSubject $subject, Workspace $workspace)
    {
        $roles = $subject instanceof \Claroline\CoreBundle\Entity\Group ?
            $this->roleRepo->findByGroupAndWorkspace($subject, $workspace):
            $this->roleRepo->findByUserAndWorkspace($subject, $workspace);

        $this->checkWorkspaceRoleEditionIsValid(array($subject), $workspace, $roles);
        $this->om->startFlushSuite();

        foreach ($roles as $role) {
            $this->dissociateRole($subject, $role);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject[]         $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function resetWorkspaceRoleForSubjects(array $subjects, $workspace)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            $this->resetWorkspaceRolesForSubject($subject, $workspace);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param array                                                    $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function initWorkspaceBaseRole(array $roles, Workspace $workspace)
    {
        $this->om->startFlushSuite();
        $entityRoles = array();

        $entityRoles['ROLE_WS_MANAGER'] = $this->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

        $this->om->endFlushSuite();

        return $entityRoles;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @throws RoleReadOnlyException
     */
    public function remove(Role $role)
    {
        if ($role->isReadOnly()) {
            throw new RoleReadOnlyException('This role cannot be removed');
        }

        $this->om->remove($role);
        $this->om->flush();
    }

    /**
     * @param array|\Claroline\CoreBundle\Entity\AbstractRoleSubject $subjects
     * @param \Claroline\CoreBundle\Entity\Role[]                    $roles
     * @param boolean                                                $sendMail
     */
    public function associateRolesToSubjects(array $subjects, array $roles, $sendMail = false)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            foreach ($roles as $role) {
                $this->associateRole($subject, $role, $sendMail);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param AbstractRoleSubject[] $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     *
     * @throws Exception\LastManagerDeleteException
     */
    public function checkWorkspaceRoleEditionIsValid(array $subjects, Workspace $workspace, array $roles)
    {
        $managerRole = $this->getManagerRole($workspace);
        $groupsManagers = $this->groupRepo->findByRoles(array($managerRole));
        $usersManagers = $this->userRepo->findByRoles(array($managerRole));

        $removedGroupsManager = 0;
        $removedUsersManager = 0;

        foreach ($subjects as $subject) {
            if ($subject->hasRole($managerRole->getName()) && in_array($managerRole, $roles)) {
                $subject instanceof \Claroline\CoreBundle\Entity\Group ?
                    $removedGroupsManager++:
                    $removedUsersManager++;
            }
        }

        if ($removedGroupsManager >= count($groupsManagers) && $removedUsersManager >= count($usersManagers)) {
            throw new LastManagerDeleteException("You can't remove every managers");
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRoles(Workspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    /**
     * @param Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceConfigurableRoles(Workspace $workspace)
    {
        $roles = $this->roleRepo->findByWorkspace($workspace);
        $configurableRoles = [];

        foreach ($roles as $role) {
            if ($role->getName() !== 'ROLE_WS_MANAGER_' . $workspace->getGuid()) {
                $configurableRoles[] = $role;
            }
        }

        return array_merge(
            $configurableRoles,
            $this->roleRepo->findBy(array('name' => 'ROLE_ANONYMOUS')),
            $this->roleRepo->findBy(array('name' => 'ROLE_USER'))
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByWorkspace(Workspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getCollaboratorRole(Workspace $workspace)
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getVisitorRole(Workspace $workspace)
    {
        return $this->roleRepo->findVisitorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getManagerRole(Workspace $workspace)
    {
        return $this->roleRepo->findManagerRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getPlatformRoles(User $user)
    {
        return $this->roleRepo->findPlatformRoles($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User                        $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRolesForUser(User $user, Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRolesForUser($user, $workspace);
    }

    /**
     * @param string $search
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesBySearchOnWorkspaceAndTag($search)
    {
        return $this->roleRepo->findByWorkspaceCodeTag($search);
    }

    /**
     * @param integer $roleId
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleById($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    /**
     * @param integer[] $ids
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByIds(array $ids)
    {
        return $this->om->findByIds('Claroline\CoreBundle\Entity\Role', $ids);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleByName($name)
    {
        return $this->roleRepo->findOneByName($name);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByName($name)
    {
        return $this->roleRepo->findByName($name);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getAllRoles()
    {
        return $this->roleRepo->findAll();
    }

    public function getAllWhereWorkspaceIsDisplayable()
    {
        return $this->roleRepo->findAllWhereWorkspaceIsDisplayable();
    }

    public function getAllWhereWorkspaceIsDisplayableAndInList(array $workspaces)
    {
        return $this->roleRepo->findAllWhereWorkspaceIsDisplayableAndInList($workspaces);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getAllPlatformRoles()
    {
        return $this->roleRepo->findAllPlatformRoles();
    }

    /**
     * @param string                                                   $key       The translation key
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleByTranslationKeyAndWorkspace($key, Workspace $workspace)
    {
        return $this->roleRepo->findOneBy(array('translationKey' => $key, 'workspace' => $workspace));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     */
    public function edit(Role $role)
    {
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * Returns the roles (an array of string) of the $token.
     *
     * @todo remove this $method
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function getStringRolesFromToken(TokenInterface $token)
    {
        $roles = array();

        foreach ($token->getRoles() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * @param string $roleName
     *
     * @return string
     */
    public function getRoleBaseName($roleName)
    {
        if ($roleName === 'ROLE_ANONYMOUS') {
            return $roleName;
        }

        $substr = explode('_', $roleName);
        $roleName = array_shift($substr);

        for ($i = 0, $countSubstr = count($substr) - 1; $i < $countSubstr; $i++) {
            $roleName .= '_' . $substr[$i];
        }

        return $roleName;
    }

    private function sendInscriptionMessage(AbstractRoleSubject $ars, Role $role)
    {
        //workspace registration
        if ($role->getWorkspace()) {
            $content = $this->translator->trans(
                'workspace_registration_message',
                array('%workspace_name%' => $role->getWorkspace()->getName()),
                'platform'
            );
            $object = $this->translator->trans(
                'workspace_registration_message_object',
                array('%workspace_name%' => $role->getWorkspace()->getName()),
                'platform'
            );
        } else {
            //new role
            $content = $this->translator->trans('new_role_message', array(), 'platform');
            $object = $this->translator->trans('new_role_message_object', array(), 'platform');
        }

        $sender = $this->container->get('security.context')->getToken()->getUser();
        $this->messageManager->sendMessageToAbstractRoleSubject($ars, $content, $object, $sender);
    }

    public function getPlatformNonAdminRoles($includeAnonymous = false)
    {
        return $this->roleRepo->findPlatformNonAdminRoles($includeAnonymous);
    }

    public function createPlatformRoleAction($translationKey)
    {
        $role = new Role();
        $role->setType($translationKey);
        $role->setName('ROLE_' . strtoupper($translationKey));
        $role->setTranslationKey($translationKey);
        $role->setReadOnly(false);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    /**
     * Returns if a role can be added to a RoleSubject.
     *
     * @param AbstractRoleSubject $ars
     * @param Role $role
     * @return bool
     */
    public function validateRoleInsert(AbstractRoleSubject $ars, Role $role)
    {
        $total = $this->countUsersByRoleIncludingGroup($role);

        //cli always win!
        if ($role->getName() === 'ROLE_ADMIN' && php_sapi_name() === 'cli' ||
            //web installer too
            $this->container->get('security.context')->getToken() === null) {
            return true;
        }

        if ($role->getName() === 'ROLE_ADMIN' && !$this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            return false;
        }

        if ($ars->hasRole($role->getName())) {
            return true;
        }

        if ($ars instanceof User) {
            return $total < $role->getMaxUsers();
        }

        if ($ars instanceof Group) {
            $userCount = $this->userRepo->countUsersOfGroup($ars);
            $userWithRoleCount = $this->userRepo->countUsersOfGroupByRole($ars, $role);

            return $total + $userCount - $userWithRoleCount < $role->getMaxUsers();
        }

        return false;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function countUsersByRoleIncludingGroup(Role $role)
    {
        return $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);
    }

    public function getRolesWithRightsByResourceNode(
        ResourceNode $resourceNode,
        $executeQuery = true
    )
    {
        return $this->roleRepo
            ->findRolesWithRightsByResourceNode($resourceNode, $executeQuery);
    }

    /**
     * This functions sets the role limit equal to the current number of users having that role
     *
     * @param Role $role
     */
    public function initializeLimit(Role $role)
    {
        $count = $this->countUsersByRoleIncludingGroup($role);
        $role->setMaxUsers($count);
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * @param Role $role
     * @param $limit
     */
    public function increaseRoleMaxUsers(Role $role, $limit)
    {
        $role->setMaxUsers($role->getMaxUsers() + $limit);
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * @param string $workspaceCode
     * @param string $translationKey
     * @param bool $executeQuery
     */
    public function getRoleByWorkspaceCodeAndTranslationKey(
        $workspaceCode,
        $translationKey,
        $executeQuery = true
    )
    {
        return $this->roleRepo->findRoleByWorkspaceCodeAndTranslationKey(
            $workspaceCode,
            $translationKey,
            $executeQuery
        );
    }

    /**
     * Returns all non-platform roles of a user.
     *
     * @param User $user The subject of the role
     *
     * @return array[Role]|query
     */
    public function getNonPlatformRolesForUser(User $user, $executeQuery = true)
    {
        return $this->roleRepo->findNonPlatformRolesForUser($user, $executeQuery);
    }

    public function getWorkspaceRoleBaseName(Role $role)
    {
        if ($role->getWorkspace()) {
            return substr($role->getName(), 0, strrpos($role->getName(), '_'));
        }

        return $role->getName();
    }

    public function getAllUserRoles($executeQuery = true)
    {
        return $this->roleRepo->findAllUserRoles($executeQuery);
    }

    public function getUserRoleByUser(User $user, $executeQuery = true)
    {
        return $this->roleRepo->findUserRoleByUser($user, $executeQuery);
    }

    public function getUserRolesByTranslationKeys(array $keys, $executeQuery = true)
    {
        return count($keys) === 0 ?
            array() :
            $this->roleRepo->findUserRolesByTranslationKeys($keys, $executeQuery);
    }

    public function getWorkspaceRoleWithToolAccess(Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRoleWithToolAccess($workspace);
    }

    public function getWorkspaceRoleByNameOrTranslationKey(
        Workspace $workspace,
        $translationKey,
        $executeQuery = true
    )
    {
        return $this->roleRepo->findWorkspaceRoleByNameOrTranslationKey(
            $workspace,
            $translationKey,
            $executeQuery
        );
    }
}
