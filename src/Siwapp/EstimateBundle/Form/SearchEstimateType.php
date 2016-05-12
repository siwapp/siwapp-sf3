<?php

namespace Siwapp\EstimateBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

use Siwapp\InvoiceBundle\Entity\Invoice;

class SearchEstimateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null, ['required' => false])
            ->add('status', ChoiceType::class, ['choices' => [
                'Draft' => Invoice::DRAFT,
                'Open' => Invoice::OPENED,
                'Overdue' => Invoice::OVERDUE,
                'Closed' => Invoice::CLOSED,
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
