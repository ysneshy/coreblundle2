<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DynamicConfigPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * Rewrites previous service definitions in order to force the dumped container to use
     * dynamic configuration parameters. Technique may vary depending on the target service
     * (see for example https://github.com/opensky/OpenSkyRuntimeConfigBundle).
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        //mailing
        $transport = new Definition();
        $transport->setClass('Swift_Transport');
        $transport->setFactoryService('claroline.mailing.transport_factory');
        $transport->setFactoryMethod('getTransport');
        $container->removeDefinition('swiftmailer.mailer.default.transport');
        $container->setDefinition('swiftmailer.mailer.default.transport', $transport);

        //session storage
        $handler = new Definition();
        $handler->setClass('SessionHandlerInterface');
        $handler->setFactoryService('claroline.session.handler_factory');
        $handler->setFactoryMethod('getHandler');
        $container->setDefinition('session.handler', $handler);

        //cookie lifetime
        if ($container->getParameter('kernel.environment') !== 'test') {
            $storage = $container->findDefinition('session.storage');
            $storage->addMethodCall('setOptions', array(new Reference('claroline.session.storage_options')));
        }

        //notification
        $container->setAlias('icap.notification.orm.entity_manager', 'claroline.persistence.object_manager');

        //facebook
        $facebook = new Definition();
        $facebook->setFactoryService('claroline.hwi.resource_owner_factory');
        $facebook->setFactoryMethod('getFacebookResourceOwner');
        $facebook->setClass('FacebookResourceOwner');
        $container->removeDefinition('hwi_oauth.resource_owner.facebook');
        $container->setDefinition('hwi_oauth.resource_owner.facebook', $facebook);
    }
}
