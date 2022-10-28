<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\NonConventionalFQCN2;

use Claroline\CoreBundle\Library\PluginBundle;

class UnexpectedBundleClassName extends PluginBundle
{
    /**
     * Invalid because the class name is not the concatenation of the vendor and bundle names
     */
}
