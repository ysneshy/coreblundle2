<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsImporter;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Transfert\Resolver;

class GroupsImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->importer = new GroupsImporter($this->om);
    }

    /**
     *  @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected, $databaseUsernames, $names)
    {
        //stub manifest
        $rolefile = __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml';
        $roles = Yaml::parse(file_get_contents($rolefile));
        $this->importer->setConfiguration(['members' => ['users' => array()], 'roles' => $roles['roles']]);

        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $groupRepo = $this->mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\Group')->andReturn($groupRepo);
        $groupRepo->shouldReceive('findNames')->andReturn($names);

        $userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\User')->andReturn($userRepo);
        $userRepo->shouldReceive('findUsernames')->andReturn($databaseUsernames);

        $data = Yaml::parse(file_get_contents($path));
        $groups['groups'] = $data['groups'];
        $this->importer->validate($groups);
    }

    public function validateProvider()
    {
        return array(
            //valid
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => false,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            //name name1 exists in the config file
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/groups/existing_name.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            //group name already exist in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array('name1')
            ),
            //username (user1) does not exists
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            //the role does not exists
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/groups/unknown_role.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
        );
    }
} 