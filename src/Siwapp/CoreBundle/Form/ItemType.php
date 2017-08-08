<?php

namespace Siwapp\CoreBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Siwapp\CoreBundle\Entity\Tax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currency = $this->manager->getRepository('SiwappConfigBundle:Property')->get('currency', 'EUR');
        $builder
            ->add('product', TextType::class, ['required' => false])
            ->add('quantity', NumberType::class, ['scale' => 2])
            ->add('discount_percent', PercentType::class, ['scale' => 2])
            ->add('description')
            ->add('unitary_cost', MoneyType::class, [
                'currency' => $currency,
                'grouping' => true,
            ])
        ;

        $builder->add('taxes', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Tax',
            'choice_label' => function (Tax $value, $key, $index) {
                return $value->label();
            },
            'multiple' => true,
            'required' => false,
        ));

        $builder->get('product')
            ->addModelTransformer(new CallbackTransformer(
                function ($product) {
                    return $product ? $product->getReference() : '';
                },
                function ($reference) {
                    $product = $this->manager
                        ->getRepository('SiwappProductBundle:Product')
                        ->findOneBy(['reference' => $reference]);

                    return $product;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\Item',
        ]);
    }
}
