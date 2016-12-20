<?php

namespace Siwapp\EstimateBundle\Form;

use Siwapp\CoreBundle\Form\AbstractInvoiceType;
use Siwapp\EstimateBundle\Entity\Estimate;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimateType extends AbstractInvoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('issue_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'form.issue_date',
                'translation_domain' => 'SiwappEstimateBundle',
            ])
            ->add('sent_by_email', null, [
                'label' => 'form.sent_by_email',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
        ;

        if (!$builder->getData()->isDraft()) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'form.status',
                'translation_domain' => 'SiwappEstimateBundle',
                'choices' => [
                    'estimate.pending' => Estimate::PENDING,
                    'estimate.approved' => Estimate::APPROVED,
                    'estimate.rejected' => Estimate::REJECTED,
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\EstimateBundle\Entity\Estimate',
        ]);
    }
}
