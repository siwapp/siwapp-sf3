<?php

namespace Siwapp\RecurringInvoiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\CoreBundle\Form\AbstractInvoiceType;

class RecurringInvoiceType extends AbstractInvoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('enabled', null, [
                'label' => 'form.enabled',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
            ])
            ->add('days_to_due', null, [
                'label' => 'form.days_to_due',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
            ])
            ->add('starting_date', DateType::class, [
                'label' => 'form.starting_date',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'widget' => 'single_text',
            ])
            ->add('finishing_date', DateType::class, [
                'label' => 'form.finishing_date',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('max_occurrences', null, [
                'label' => 'form.max_occurrences',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'attr' => ['min' => 1],
            ])
            ->add('period', null, [
                'label' => 'form.period',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'attr' => ['min' => 1],
            ])
            ->add('period_type', ChoiceType::class, [
                'label' => 'form.period_type',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'choices' => [
                    'form.day' => 'day',
                    'form.week' => 'week',
                    'form.month' => 'month',
                    'form.year' => 'year',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice',
        ]);
    }
}
