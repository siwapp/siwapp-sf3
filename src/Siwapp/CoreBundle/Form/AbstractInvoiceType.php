<?php

namespace Siwapp\CoreBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Siwapp\CoreBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractInvoiceType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer_name', null, [
                'label' => 'form.customer_name',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('customer_identification', null, [
                'label' => 'form.customer_identification',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('customer_email', null, [
                'label' => 'form.customer_email',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('invoicing_address', null, [
                'attr' => ['rows' => 3],
                'label' => 'form.invoicing_address',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('shipping_address', null, [
                'attr' => ['rows' => 3],
                'label' => 'form.shipping_address',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('contact_person', null, [
                'label' => 'form.contact_person',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('terms', null, [
                'attr' => ['rows' => 5],
                'label' => 'form.terms',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('notes', null, [
                'attr' => ['rows' => 5],
                'label' => 'form.notes',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
        ;

        $builder->add('items', CollectionType::class, array(
            'entry_type' => 'Siwapp\CoreBundle\Form\ItemType',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
            'label' => false,
            'prototype_data' => new Item($this->manager->getRepository('SiwappCoreBundle:Tax')->findBy(['is_default' => 1])),
        ));

        $builder->add('series', EntityType::class, array(
            'class' => 'SiwappCoreBundle:Series',
            'choice_label' => 'name',
            'label' => 'form.series',
            'translation_domain' => 'SiwappInvoiceBundle',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\CoreBundle\Entity\AbstractInvoice',
        ]);
    }
}
