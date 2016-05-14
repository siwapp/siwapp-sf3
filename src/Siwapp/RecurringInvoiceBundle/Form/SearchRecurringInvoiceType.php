<?php

namespace Siwapp\RecurringInvoiceBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

use Siwapp\RecurringInvoiceBundle\Entity\RecurringInvoice;

class SearchRecurringInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null, ['required' => false])
            ->add('status', ChoiceType::class, ['choices' => [
                'Inactive' => RecurringInvoice::INACTIVE,
                'Active' => RecurringInvoice::ACTIVE,
                'Pending' => RecurringInvoice::PENDING,
                'Finished' => RecurringInvoice::FINISHED,
            ],'required' => false])
            ->add('customer', null, ['required' => false])
        ;

        $builder->add('serie', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Serie',
            'choice_label' => 'name',
            'placeholder' => '-',
            'required' => false,
        ));
    }
}
