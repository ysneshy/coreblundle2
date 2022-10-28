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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Constraints\WorkspaceUniqueCode;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceType extends AbstractType
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;

        $builder
            ->add('name', 'text')
            ->add(
                'code',
                'text',
                array('constraints' => array(new WorkspaceUniqueCode()))
            )->add(
                'description',
                isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce'] ?
                    'textarea' :
                    'tinymce',
                array('required' => false)
            )
            ->add(
                'model',
                'entity',
                array(
                    'class' => 'ClarolineCoreBundle:Model\WorkspaceModel',
                    'query_builder' => function (EntityRepository $er) use ($user) {

                        return $er->createQueryBuilder('wm')
                            ->join('wm.users', 'u')
                            ->where('u.id = :userId')
                            ->setParameter('userId', $user->getId())
                            ->orderBy('wm.name', 'ASC');
                    },
                    'property' => 'name',
                    'required' => false
                )
            )
            ->add('displayable', 'checkbox', array('required' => false))
            ->add('selfRegistration', 'checkbox', array('required' => false))
            ->add('registrationValidation', 'checkbox', array('required' => false))
            ->add('selfUnregistration', 'checkbox', array('required' => false));
    }

    public function getName()
    {
        return 'workspace_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
