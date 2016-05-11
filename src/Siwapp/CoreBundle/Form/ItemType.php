<?php

namespace Siwapp\CoreBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', NumberType::class)
            ->add('discount', PercentType::class)
            ->add('description')
            ->add('unitary_cost', MoneyType::class)
        ;

        $builder->add('taxes', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Tax',
            'choice_label' => 'name',
            'expanded' => true,
            'multiple' => true,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\Item',
        ]);
    }
}
