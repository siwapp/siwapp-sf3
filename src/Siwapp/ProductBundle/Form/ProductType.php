<?php

namespace Siwapp\ProductBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', null, [
                'label' => 'form.reference',
                'translation_domain' => 'SiwappProductBundle',
            ])
            ->add('description', null, [
                'label' => 'form.description',
                'translation_domain' => 'SiwappProductBundle',
            ])
            ->add('price', null, [
                'label' => 'form.price',
                'translation_domain' => 'SiwappProductBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\ProductBundle\Entity\Product',
        ]);
    }
}
