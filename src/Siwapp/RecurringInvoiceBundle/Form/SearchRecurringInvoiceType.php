<?php

namespace Siwapp\RecurringInvoiceBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

class SearchRecurringInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null, [
                'required' => false,
                'label' => 'search.terms',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'search.status',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'choices' => [
                    'recurring_invoice.inactive' => RecurringInvoice::INACTIVE,
                    'recurring_invoice.active' => RecurringInvoice::ACTIVE,
                    'recurring_invoice.pending' => RecurringInvoice::PENDING,
                    'recurring_invoice.finished' => RecurringInvoice::FINISHED,
                ],
                'required' => false])
            ->add('customer', null, [
                'label' => 'search.customer',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'required' => false,
            ])
        ;

        $builder->add('series', EntityType::class, [
            'label' => 'search.series',
            'translation_domain' => 'SiwappRecurringInvoiceBundle',
            'class' => 'SiwappCoreBundle:Series',
            'choice_label' => 'name',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
