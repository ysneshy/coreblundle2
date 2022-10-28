<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;

class LoadGroupData extends AbstractFixture implements ContainerAwareInterface
{
    private $groups;
    private $container;

    /**
     * Constructor. Expects an associative array where each key is an unique group name
     * and each value is an array of username). Users must have been loaded
     * and referenced in a previous fixtures with a 'user/[username]' label.
     *
     * For each group, 1 fixture reference will be added:
     * - role/[group's name] (group's role)
     *
     * @param array $users
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->groups as $name => $users) {
            $group = new Group();
            $group->setName($name);

            foreach ($users as $username) {
                $user = $this->getReference('user/'.$username);
                $group->addUser($user);
            }

            $manager->persist($group);
            $this->addReference('group/'.$name, $group);
        }

        $manager->flush();
    }
}
