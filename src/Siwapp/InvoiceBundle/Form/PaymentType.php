<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'payment.form.date',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'payment.form.amount',
                'translation_domain' => 'SiwappInvoiceBundle',
                'grouping' => true,
            ])
            ->add('notes', null, [
                'label' => 'payment.form.notes',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\InvoiceBundle\Entity\Payment',
        ]);
    }
}
