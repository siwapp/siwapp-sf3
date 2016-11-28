<?php

namespace Siwapp\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'series.form.name',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('value', TextType::class, [
                'required' => true,
                'label' => 'series.form.value',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('first_number', NumberType::class, [
                'required' => true,
                'label' => 'series.form.first_number',
                'translation_domain' => 'SiwappCoreBundle',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'series.form.enabled',
                'translation_domain' => 'SiwappCoreBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\Series',
        ]);
    }
}
