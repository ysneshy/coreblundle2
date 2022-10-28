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

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsConfigurationBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;

/**
 * @DI\Service("claroline.manager.transfert_manager")
 */
class TransfertManager
{
    private $listImporters;
    private $rootPath;
    private $om;
    private $container;
    private $data;
    private $workspace;

    /**
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($om, $container)
    {
        $this->listImporters = new ArrayCollection();
        $this->om = $om;
        $this->container = $container;
        $this->data = array();
        $this->workspace = null;
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    /**
     * Import a workspace
     */
    public function validate(array $data, $validateProperties = true)
    {
        $groupsImporter = $this->getImporterByName('groups');
        $rolesImporter  = $this->getImporterByName('roles');
        $toolsImporter  = $this->getImporterByName('tools');
        $importer       = $this->getImporterByName('workspace_properties');
        $usersImporter  = $this->getImporterByName('user');

        //properties
        if ($validateProperties) {
            if (isset($data['properties'])) {
                $properties['properties'] = $data['properties'];
                $importer->validate($properties);
            }
        }

        if (isset($data['roles'])) {
            $roles['roles'] = $data['roles'];
            $rolesImporter->validate($roles);
        }

        if (isset ($data['tools'])) {
            $tools['tools'] = $data['tools'];
            $toolsImporter->validate($tools);
        }

    }

    public function import(Configuration $configuration)
    {
        $owner = $this->container->get('security.context')->getToken()->getUser();
        $configuration->setOwner($owner);
        $this->setImporters($configuration, $data);
        $this->validate($data);

        //initialize the configuration
        $configuration->setWorkspaceName($data['properties']['name']);
        $configuration->setWorkspaceCode($data['properties']['code']);
        $configuration->setDisplayable($data['properties']['visible']);
        $configuration->setSelfRegistration($data['properties']['self_registration']);
        $configuration->setSelfUnregistration($data['properties']['self_unregistration']);

        $this->createWorkspace($configuration, $owner, true);
    }

    /**
     * Populates a workspace content with the content of an zip archive. In other words, it ignores the
     * many properties of the configuration object and use an existing workspace as base.
     *
     * This will set the $this->data var
     * This will set the $this->workspace var
     *
     * @param Workspace $workspace
     * @param Confuguration $configuration
     * @param Directory $root
     * @param array $entityRoles
     * @param bool $isValidated
     * @param bool $importRoles
     */
    public function populateWorkspace(
        Workspace $workspace,
        Configuration $configuration,
        Directory $root,
        array $entityRoles,
        $isValidated = false,
        $importRoles = true
    )
    {
        $this->om->startFlushSuite();
        $data = $configuration->getData();
        //refactor how workspace are created because this sucks
        $this->data = $configuration->getData();
        $this->workspace = $workspace;
        $this->setImporters($configuration, $data);
        $this->setWorkspaceForImporter($workspace);

        if (!$isValidated) {
            $this->validate($data, false);
        }

        if ($importRoles) {
            $importedRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
        }

        foreach ($entityRoles as $key => $entityRole) {
            $importedRoles[$key] = $entityRole;
        }

        $tools = $this->getImporterByName('tools')->import($data['tools'], $workspace, $importedRoles, $root);
        $this->om->endFlushSuite();
    }

    /**
     * @param Configuration $configuration
     * @param User $owner
     * @param bool $isValidated
     *
     * @throws InvalidConfigurationException
     * @return SimpleWorkbolspace
     *
     * The template doesn't need to be validated anymore if
     *  - it comes from the self::import() function
     *  - we want to create a user from the default template (it should work no matter what)
     */
    public function createWorkspace(
        Configuration $configuration,
        User $owner,
        $isValidated = false
    )
    {
        $configuration->setOwner($owner);
        $data = $configuration->getData();
        $this->data = $data;
        $this->om->startFlushSuite();
        $this->setImporters($configuration, $data);

        if (!$isValidated) {
            $this->validate($data, false);
            $isValidated = true;
        }

        $workspace = new Workspace();
        $workspace->setName($configuration->getWorkspaceName());
        $workspace->setCode($configuration->getWorkspaceCode());
        $workspace->setDescription($configuration->getWorkspaceDescription());
        $workspace->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        $workspace->setDisplayable($configuration->isDisplayable());
        $workspace->setSelfRegistration($configuration->getSelfRegistration());
        $workspace->setSelfUnregistration($configuration->getSelfUnregistration());
        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());

        if ($owner) {
            $workspace->setCreator($owner);
        }

        $this->om->persist($workspace);
        $this->om->flush();

        //load roles
        $entityRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
        //The manager role is required for every workspace
        $entityRoles['ROLE_WS_MANAGER'] = $this->container->get('claroline.manager.role_manager')->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

        $owner->addRole($entityRoles['ROLE_WS_MANAGER']);
        $this->om->persist($owner);

        //add base roles to the role array
        $pfRoles = $this->om->getRepository('ClarolineCoreBundle:Role')->findAllPlatformRoles();

        foreach ($pfRoles as $pfRole) {
            $entityRoles[$pfRole->getName()] = $pfRole;
        }

        $entityRoles['ROLE_ANONYMOUS'] = $this->om
            ->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ANONYMOUS');
        $entityRoles['ROLE_USER'] = $this->om
            ->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');


        $dir = new Directory();
        $dir->setName($workspace->getName());
        $dir->setIsUploadDestination(true);

        $root = $this->container->get('claroline.manager.resource_manager')->create(
            $dir,
            $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByName('directory'),
            $owner,
            $workspace,
            null,
            null,
            array()
        );

        $this->populateWorkspace($workspace, $configuration, $root, $entityRoles, true, false);
        $this->container->get('claroline.manager.workspace_manager')->createWorkspace($workspace);
        $this->om->endFlushSuite();

        return $workspace;
    }

    //refactor how workspace are created because this sucks
    public function importRichText()
    {
        //now we have to parse everything in case there is a rich text
        //rich texts must be located in the tools section
        $data = $this->data;
        $this->container->get('claroline.importer.rich_text_formatter')->setData($data);
        $this->container->get('claroline.importer.rich_text_formatter')->setWorkspace($this->workspace);

        foreach ($data['tools'] as $tool) {
            $importer = $this->getImporterByName($tool['tool']['type']);
            if (!$importer) {
                throw new InvalidConfigurationException('The importer ' . $tool['tool']['type'] . ' does not exist');
            }

            if (isset($tool['tool']['data']) && $importer instanceof RichTextInterface) {
                $data['data'] = $tool['tool']['data'];
                $importer->format($data);
            }
        }

        $this->om->flush();
    }

    private function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    private function getImporterByName($name)
    {
        foreach ($this->listImporters as $importer) {
            if ($importer->getName() === $name) {
                return $importer;
            }
        }

        return null;
    }

    public function export(Workspace $workspace)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setListImporters($this->listImporters);
        }

        $data = [];
        $files = [];
        $data['roles'] = $this->getImporterByName('roles')->export($workspace, $files, null);
        $data['tools'] = $this->getImporterByName('tools')->export($workspace, $files, null);

        //generate the archive in a temp dir
        $content = Yaml::dump($data, 10);
        //zip and returns the archive
        $archDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $archPath = $archDir . DIRECTORY_SEPARATOR . 'archive.zip';
        mkdir($archDir);
        $manifestPath = $archDir . DIRECTORY_SEPARATOR . 'manifest.yml';
        file_put_contents($manifestPath, $content);
        $archive = new \ZipArchive();
        $success = $archive->open($archPath, \ZipArchive::CREATE);

        if ($success === true) {
            $archive->addFile($manifestPath, 'manifest.yml');

            foreach ($files as $uid => $file) {
                $archive->addFile($file, $uid);
            }

            $archive->close();
        } else {
            throw new \Exception('Unable to create archive . ' . $archPath . ' (error ' . $success . ')');
        }

        return $archPath;
    }

    /**
     * Inject the rootPath
     *
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $configuration
     * @param array $data
     * @param $isStrict
     */
    private function setImporters(Configuration $configuration, array $data)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($configuration->getExtractPath());
            if ($owner = $configuration->getOwner()) {
                $importer->setOwner($owner);
            } else {
                $importer->setOwner($this->container->get('security.context')->getToken()->getUser());
            }
            $importer->setConfiguration($data);
            $importer->setListImporters($this->listImporters);
        }
    }

    private function setWorkspaceForImporter(Workspace $workspace)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setWorkspace($workspace);
        }
    }

    public function dumpConfiguration()
    {
        $dumper = new YamlReferenceDumper($this->importer);

        $string = '';
        $string .= $dumper->dump($this->getImporterByName('workspace_properties'));
        $string .= $dumper->dump($this->getImporterByName('owner'));
        $string .= $dumper->dump($this->getImporterByName('user'));
        $string .= $dumper->dump($this->getImporterByName('groups'));
        $string .= $dumper->dump($this->getImporterByName('roles'));
        $string .= $dumper->dump($this->getImporterByName('tools'));
        $string .= $dumper->dump($this->getImporterByName('forum'));

        return $string;
    }
}
