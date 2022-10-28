<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Claroline\CoreBundle\Form\Badge\Type\BadgePickerBadgeType;
use Claroline\CoreBundle\Form\DataTransformer\BadgePickerTransformer;
use Claroline\CoreBundle\Manager\BadgeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.badgepicker")
 * @DI\FormType(alias = "badgepicker")
 */
class BadgePickerType extends AbstractType
{
    /**
     * @var BadgePickerTransformer
     */
    private $badgePickerTransformer;

    /**
     * @DI\InjectParams({
     *     "badgePickerTransformer" = @DI\Inject("claroline.transformer.badge_picker")
     * })
     */
    public function __construct(BadgePickerTransformer $badgePickerTransformer)
    {
        $this->badgePickerTransformer = $badgePickerTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->badgePickerTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multiple']    = $options['multiple'];
        $view->vars['mode']        = $options['mode'];
        $view->vars['workspace']   = $options['workspace'];
        $view->vars['blacklist']   = $options['blacklist'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple'     => false,
                'mode'         => BadgeManager::BADGE_PICKER_DEFAULT_MODE,
                'workspace'    => null,
                'blacklist'    => array()
            )
        );
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'badgepicker';
    }
}
