<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.daterange")
 * @DI\FormType(alias = "daterange")
 */
class DateRangeType extends AbstractType
{
    protected $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new DateRangeToTextTransformer($this->translator));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'daterange';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'platform'
            )
        );
    }
}
