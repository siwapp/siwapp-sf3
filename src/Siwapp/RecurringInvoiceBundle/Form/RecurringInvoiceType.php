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
            ->add('enabled')
            ->add('days_to_due')
            ->add('starting_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('finishing_date', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('max_occurrences', null, [
                'attr' => ['min' => 1],
            ])
            ->add('period', null, [
                'label' => 'Create every',
                'attr' => ['min' => 1],
            ])
            ->add('period_type', ChoiceType::class, [
                'choices' => [
                    'Days' => 'day',
                    'Weeks' => 'week',
                    'Months' => 'month',
                    'Years' => 'year',
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
