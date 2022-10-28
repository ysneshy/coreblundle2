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

class Updater021100
{
    private $container;
    private $logger;
    private $om;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->log('Updating default mails layout...');
        $repository = $this->om->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');

        $frLayout = '<div></div>%content%<div></hr><p>Ce mail vous a été envoyé par %first_name% %last_name%</p>';
        $frLayout .= '<p>Powered by %platform_name%</p></div>';
        $enLayout = '<div></div>%content%<div></hr><p>This mail was sent to you by %first_name% %last_name%</p>';
        $enLayout .= '<p>Powered by %platform_name%</p></div>';

        $layout = $this->om->getRepository('ClarolineCoreBundle:Content')->findOneByType('claro_mail_layout');
        $layout->setType('claro_mail_layout');
        $layout->setContent($enLayout);
        $repository->translate($layout, 'content', 'fr', $frLayout);
        $this->om->persist($layout);

        $this->om->flush();
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