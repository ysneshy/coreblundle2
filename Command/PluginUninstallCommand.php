<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Uninstalls a plugin.
 */
class PluginUninstallCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:plugin:uninstall')
            ->setDescription('Uninstalls a specified claroline plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $this->getPlugin($input);
        $this->getPluginInstaller($output)->uninstall($plugin);
        $this->resetCache($output);
    }
}
