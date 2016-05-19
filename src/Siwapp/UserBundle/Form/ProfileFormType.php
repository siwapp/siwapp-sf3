<?php

namespace Siwapp\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('locale', LocaleType::class, [
            'placeholder'=> 'form.placeholder',
            'label' => 'form.locale',
            'translation_domain' => 'SiwappUserBundle',
        ]);
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}
