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
            ->add('issue_date', DateType::class, ['widget' => 'single_text'])
        ;

        if (!$builder->getData()->isDraft()) {
            $builder->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => Estimate::PENDING,
                    'Approved' => Estimate::APPROVED,
                    'Rejected' => Estimate::REJECTED,
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
