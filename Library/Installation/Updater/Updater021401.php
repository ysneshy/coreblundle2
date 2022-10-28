<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

class Updater021401
{
    private $container;
    private $logger;
    /** @var ObjectManager */
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateIcons();
    }

    public function updateIcons()
    {
        $this->log('updating icons...');

        $mimetypes = array('application/illustrator', 'application/ai');

        foreach ($mimetypes as $mimetype) {
            $icon = new ResourceIcon();
            $icon->setRelativeUrl('bundles/clarolinecore/images/resources/icons/res_vector.png');
            $icon->setMimeType($mimetype);
            $icon->setShortcut(false);
            $this->om->persist($icon);

            $this->container->get('claroline.manager.icon_manager')->createShortcutIcon($icon);
        }
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
