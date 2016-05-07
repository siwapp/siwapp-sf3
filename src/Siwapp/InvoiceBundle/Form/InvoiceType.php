<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\CoreBundle\Form\AbstractInvoiceType;
use Siwapp\InvoiceBundle\Entity\Item;
use Siwapp\InvoiceBundle\Form\ItemType;


class InvoiceType extends AbstractInvoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('draft', HiddenType::class)
            ->add('closed', HiddenType::class)
            ->add('sent_by_email', HiddenType::class)
            ->add('number')
            ->add('recurring_invoice_id', HiddenType::class)
            ->add('issue_date', DateType::class, array('widget' => 'single_text'))
            ->add('due_date', DateType::class, array('widget' => 'single_text'))
        ;

        $builder->add('items', CollectionType::class, array(
            'entry_type' => 'Siwapp\InvoiceBundle\Form\ItemType',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\InvoiceBundle\Entity\Invoice',
        ]);
    }
}
