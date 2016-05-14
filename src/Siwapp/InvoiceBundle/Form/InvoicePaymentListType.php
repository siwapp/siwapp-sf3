<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\InvoiceBundle\Entity\Payment;

class InvoicePaymentListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('payments', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $builder->getData(),
                'choice_label' => function ($payment) {
                    return ' ';
                },
                'choice_value' => function ($payment) {
                    return $payment->getId();
                },
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => 'Siwapp\InvoiceBundle\Entity\Invoice',
        ]);
    }
}
