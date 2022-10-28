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
use Claroline\CoreBundle\Entity\Home\Region;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadRegionData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $names = array('header', 'left', 'content', 'right', 'footer');

        foreach ($names as $name) {
            $region = new Region();

            $region->setName($name);

            $manager->persist($region);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
