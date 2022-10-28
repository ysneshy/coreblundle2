<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\UnexpectedRoutingPrefix3;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedRoutingPrefix3 extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return "\rInvalid\trouting\n prefix";
    }
}
