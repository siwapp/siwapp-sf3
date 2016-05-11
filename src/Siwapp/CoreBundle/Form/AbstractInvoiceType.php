<?php

namespace Siwapp\CoreBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer_id', HiddenType::class)
            ->add('customer_name')
            ->add('customer_identification')
            ->add('customer_email')
            ->add('invoicing_address', null, [
                'attr' => ['rows' => 3],
            ])
            ->add('shipping_address', null, [
                'attr' => ['rows' => 3],
            ])
            ->add('contact_person')
            ->add('terms', null, ['attr' => ['rows' => 5]])
            ->add('notes', null, ['attr' => ['rows' => 5]])
        ;

        $builder->add('items', CollectionType::class, array(
            'entry_type' => 'Siwapp\CoreBundle\Form\ItemType',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
            'label' => false,
        ));

        $builder->add('serie', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Serie',
            'choice_label' => 'name',
            'placeholder' => '-',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\AbstractInvoice',
        ]);
    }
}
