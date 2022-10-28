<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadThemeData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $themes = array(
            'Claroline' => 'ClarolineCoreBundle:less:claroline/theme.html.twig',
            'Claroline Orange' => 'ClarolineCoreBundle:less:claroline-orange/theme.html.twig',
            'Claroline Mint' => 'ClarolineCoreBundle:less:claroline-mint/theme.html.twig',
            'Claroline Gold' => 'ClarolineCoreBundle:less:claroline-gold/theme.html.twig',
            'Claroline Ruby' => 'ClarolineCoreBundle:less:claroline-ruby/theme.html.twig',
            'Claroline Black' => 'ClarolineCoreBundle:less:claroline-black/theme.html.twig',
            'Claroline Dark' => 'ClarolineCoreBundle:less:claroline-dark/theme.html.twig',
            'Bootstrap Default' => 'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig'
        );

        foreach ($themes as $name => $path) {
            $theme[$name] = new Theme();
            $theme[$name]->setName($name);
            $theme[$name]->setPath($path);

            $manager->persist($theme[$name]);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
