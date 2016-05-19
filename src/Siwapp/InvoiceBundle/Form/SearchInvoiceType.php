<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\InvoiceBundle\Entity\Invoice;

class SearchInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null, [
                'required' => false,
                'label' => 'search.terms',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'search.status',
                'translation_domain' => 'SiwappInvoiceBundle',
                'choices' => [
                    'invoice.draft' => Invoice::DRAFT,
                    'invoice.opened' => Invoice::OPENED,
                    'invoice.overdue' => Invoice::OVERDUE,
                    'invoice.closed' => Invoice::CLOSED,
                ],
                'required' => false,
            ])
            ->add('date_from', DateType::class, [
                'label' => 'search.date_from',
                'translation_domain' => 'SiwappInvoiceBundle',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('date_to', DateType::class, [
                'label' => 'search.date_to',
                'translation_domain' => 'SiwappInvoiceBundle',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('customer', null, [
                'label' => 'search.customer',
                'translation_domain' => 'SiwappInvoiceBundle',
                'required' => false,
            ])
        ;

        $builder->add('series', EntityType::class, [
            'label' => 'search.series',
            'translation_domain' => 'SiwappInvoiceBundle',
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
