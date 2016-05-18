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
                    'Inactive' => RecurringInvoice::INACTIVE,
                    'Active' => RecurringInvoice::ACTIVE,
                    'Pending' => RecurringInvoice::PENDING,
                    'Finished' => RecurringInvoice::FINISHED,
                ],
                'required' => false])
            ->add('customer', null, [
                'label' => 'search.customer',
                'translation_domain' => 'SiwappRecurringInvoiceBundle',
                'required' => false,
            ])
        ;

        $builder->add('serie', EntityType::class, [
            'label' => 'search.series',
            'translation_domain' => 'SiwappRecurringInvoiceBundle',
            'class' => 'SiwappCoreBundle:Serie',
            'choice_label' => 'name',
            'placeholder' => '-',
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
