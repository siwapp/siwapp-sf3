<?php

namespace Siwapp\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('series_id', ChoiceType::class)
            ->add('customer_id', HiddenType::class)
            ->add('customer_name')
            ->add('customer_identification')
            ->add('customer_email', EmailType::class)
            ->add('invoicing_address', TextareaType::class)
            ->add('shipping_address', TextareaType::class)
            ->add('contact_person')
            ->add('terms', TextareaType::class)
            ->add('notes', TextareaType::class)
            ->add('status', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\AbstractInvoice',
        ]);
    }
}
