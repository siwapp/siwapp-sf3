<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\CoreBundle\Form\AbstractInvoiceType;
use Siwapp\InvoiceBundle\Entity\Item;
use Siwapp\InvoiceBundle\Form\ItemType;

class InvoiceType extends AbstractInvoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('issue_date', DateType::class, ['widget' => 'single_text'])
            ->add('due_date', DateType::class, ['widget' => 'single_text'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\InvoiceBundle\Entity\Invoice',
        ]);
    }
}
