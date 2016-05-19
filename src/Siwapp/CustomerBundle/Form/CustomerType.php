<?php

namespace Siwapp\CustomerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
            ->add('identification', null, [
                'label' => 'form.identification',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
            ->add('email', null, [
                'label' => 'form.email',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
            ->add('contact_person', null, [
                'label' => 'form.contact_person',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
            ->add('invoicing_address', TextareaType::class, [
                'required' => false,
                'label' => 'form.invoicing_address',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
            ->add('shipping_address', TextareaType::class, [
                'required' => false,
                'label' => 'form.shipping_address',
                'translation_domain' => 'SiwappCustomerBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CustomerBundle\Entity\Customer',
        ]);
    }
}
