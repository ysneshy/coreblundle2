<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Claroline\CoreBundle\Validator\Constraints\FileSize;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GeneralType extends AbstractType
{
    private $langs;
    private $role;
    private $description;
    private $dateFormat;
    private $language;

    public function __construct(array $langs, $role, $description, $dateFormat, $language)
    {
        $this->role = $role;
        $this->description = $description;
        $this->dateFormat  = $dateFormat;
        $this->language    = $language;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => false))
            ->add(
                'description',
                'content',
                array(
                    'data' => $this->description,
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Description',
                    'theme_options' => array('contentTitle' => false, 'tinymce' => false)
                )
            )
            ->add('support_email', 'email', array('label' => 'support_email'))
            ->add('selfRegistration', 'checkbox', array('required' => false))
            ->add(
                'defaultRole',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->role,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            )
            ->add(
                'localeLanguage',
                'choice',
                array(
                    'choices' => $this->langs
                )
            )
            ->add('formCaptcha', 'checkbox', array('label' => 'display_captcha', 'required' => false))
            ->add('redirect_after_login', 'checkbox', array('label' => 'redirect_after_login', 'required' => false))
            ->add('account_duration', 'integer', array('label' => 'account_duration_label', 'required' => false))
            ->add('anonymous_public_profile', 'checkbox', array('label' => 'show_profile_for_anonymous', 'required' => false))
            ->add('portfolio_url', 'url', array('label' => 'portfolio_url', 'required' => false))
            ->add('isNotificationActive', 'checkbox', array('label' => 'activate_notifications', 'required' => false))
            ->add('maxStorageSize', 'text', array('required' => false, 'label' => 'max_storage_size', 'constraints' => array(new FileSize())))
            ->add('maxUploadResources', 'integer', array('required' => false, 'label' => 'count_resources'));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfiguration $generalParameters */
            $generalParameters = $event->getData();
            $form = $event->getForm();

            $form
                ->add('platform_init_date', 'datepicker', array(
                        'input'       => 'timestamp',
                        'label'       => 'platform_init_date',
                        'required'    => false,
                        'format'      => $this->dateFormat,
                        'language'    => $this->language
                    )
                )
                ->add('platform_limit_date', 'datepicker', array(
                        'input'       => 'timestamp',
                        'label'       => 'platform_expiration_date',
                        'required'    => false,
                        'format'      => $this->dateFormat,
                        'language'    => $this->language
                    )
                );
        });
   }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'translation_domain' => 'platform',
                'date_format'        => DateType::HTML5_FORMAT
            )
        );
    }
}
