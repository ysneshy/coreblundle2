<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateRange extends Constraint
{
    public $message = 'invalid_date_range';

    public function validatedBy()
    {
        return 'daterange_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
} 