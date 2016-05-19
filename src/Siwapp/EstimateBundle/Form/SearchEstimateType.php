<?php

namespace Siwapp\EstimateBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\EstimateBundle\Entity\Estimate;

class SearchEstimateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null, [
                'required' => false,
                'label' => 'search.terms',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'estimate.draft' => Estimate::DRAFT,
                    'estimate.pending' => Estimate::PENDING,
                    'estimate.approved' => Estimate::APPROVED,
                    'estimate.rejected' => Estimate::REJECTED,
                ],
                'required' => false,
                'label' => 'search.status',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
            ->add('date_from', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'search.date_from',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
            ->add('date_to', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'search.date_to',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
            ->add('customer', null, [
                'required' => false,
                'label' => 'search.customer',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
        ;

        $builder->add('series', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Series',
            'choice_label' => 'name',
            'required' => false,
            'label' => 'search.series',
            'translation_domain' => 'SiwappEstimateBundle',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
