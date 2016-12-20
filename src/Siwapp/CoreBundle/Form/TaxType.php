<?php

namespace Siwapp\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'tax.form.name',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('value', NumberType::class, [
                'required' => false,
                'label' => 'tax.form.value',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'tax.form.active',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('is_default', CheckboxType::class, [
                'required' => false,
                'label' => 'tax.form.is_default',
                'translation_domain' => 'SiwappCoreBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\Tax',
        ]);
    }
}
