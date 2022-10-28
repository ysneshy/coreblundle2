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

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * @Service("claroline.common.authentication_manager")
 */
class AuthenticationManager
{
    private $container;
    private $driverPath;
    private $finder;

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->fileTypes = '/\.yml$/';
        $this->driverPath = __DIR__ . "/../../../../../../app/config/Authentication/";
        $this->finder = new Finder();
    }

    /**
     * Get authentication drivers
     */
    public function getDrivers()
    {
        $drivers = array();

        $files = $this->finder->files()->in($this->driverPath)->name($this->fileTypes);

        foreach ($files as $file) {
            $driver = str_replace('.yml', '', $file->getRelativePathname());
            $service = $this->getService($driver);

            if ($service and $servers = $service->getServers()) {
                foreach ($servers as $server) {
                    $drivers[$driver . ':' . $server] = $driver . ':' . $server;
                }
            }
        }

        return $drivers;
    }

    /**
     * Authenticate
     *
     * @param $driver The name of the driver including the server, example: claroline.ldap:server1
     */
    public function authenticate($driver, $user, $password)
    {
        $service = $this->getService($driver);

        if ($service and $service->authenticate($this->getServerName($driver), $user, $password)) {
            return true;
        }
    }

    /**
     * Return authentication driver manager
     *
     * @param $driver The name of the driver including the server, example: claroline.ldap:server1
     */
    public function getService($driver)
    {
        if ($driver = explode(':', $driver) and
            isset($driver[0]) and
            $driver = explode('.', $driver[0]) and
            isset($driver[1])
        ) {
            return $this->container->get($driver[0] . '.' . $driver[1] . '_bundle.manager.' . $driver[1] . '_manager');
        }
    }

    /**
     * Return server name
     *
     * @param $driver The name of the driver including the server, example: claroline.ldap:server1
     */
    public function getServerName($driver)
    {
        if ($driver = explode(':', $driver) and isset($driver[1])) {
            return $driver[1];
        }
    }

}

