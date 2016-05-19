<?php

namespace Siwapp\ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

use Siwapp\CoreBundle\Form\TaxType;
use Siwapp\CoreBundle\Form\SerieType;

class GlobalSettingsType extends AbstractType
{
    protected static $paper_sizes = array(
        "4a0" => "4A0", "2a0" => "2A0", "a0" => "A0", "a1" => "A1", "a2" => "A2", "a3" => "A3", "a4" => "A4", "a5" => "A5", "a6" => "A6", "a7" => "A7", "a8" => "A8", "a9" => "A9", "a10" => "A10", "b0" => "B0", "b1" => "B1", "b2" => "B2", "b3" => "B3", "b4" => "B4", "b5" => "B5", "b6" => "B6", "b7" => "B7", "b8" => "B8", "b9" => "B9", "b10" => "B10", "c0" => "C0", "c1" => "C1", "c2" => "C2", "c3" => "C3", "c4" => "C4", "c5" => "C5", "c6" => "C6", "c7" => "C7", "c8" => "C8", "c9" => "C9", "c10" => "C10", "ra0" => "RA0", "ra1" => "RA1", "ra2" => "RA2", "ra3" => "RA3", "ra4" => "RA4", "sra0" => "SRA0", "sra1" => "SRA1", "sra2" => "SRA2", "sra3" => "SRA3", "sra4" => "SRA4", "letter" => "Letter", "legal" => "Legal", "ledger" => "Ledger", "tabloid" => "Tabloid", "executive" => "Executive", "folio" => "Folio", "commerical #10 envelope" => "Commercial #10 Envelope", "catalog #10 1/2 envelope" => "Catalog #10 1/2 Envelope", "8.5x11" => "8.5x11", "8.5x14" => "8.5x14", "11x17" => "11x17"
    );

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company_name', TextType::class, [
                'required' => false,
                'label' => 'form.company_name',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_address', TextareaType::class, [
                'required' => false,
                'label' => 'form.company_address',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_phone', TextType::class, [
                'required' => false,
                'label' => 'form.company_phone',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_fax', TextType::class, [
                'required' => false,
                'label' => 'form.company_fax',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_email', EmailType::class, [
                'label' => 'form.company_email',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_url', UrlType::class, [
                'required' => false,
                'label' => 'form.company_url',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('company_logo', FileType::class, [
                'required' => false,
                'label' => 'form.company_logo',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('currency', CurrencyType::class, [
                'label' => 'form.currency',
                'translation_domain' => 'SiwappConfigBundle',
                'preferred_choices' => ['EUR', 'USD', 'RUB', 'LVL', 'LTL'],
            ])
            ->add('legal_terms', TextareaType::class, [
                'required' => false,
                'label' => 'form.legal_terms',
                'translation_domain' => 'SiwappConfigBundle',
            ])
            ->add('pdf_size', ChoiceType::class, [
                'required' => false,
                'label' => 'form.pdf_size',
                'translation_domain' => 'SiwappConfigBundle',
                'choices' => array_flip(self::$paper_sizes),
            ])
            ->add('pdf_orientation', ChoiceType::class, [
                'label' => 'form.pdf_orientation',
                'translation_domain' => 'SiwappConfigBundle',
                'choices' => array(
                    'form.pdf_portrait' => 'Portrait',
                    'form.pdf_landscape' => 'Landscape',
                )
            ])
        ;

        $builder->add('taxes', CollectionType::class, array(
            'label' => 'form.taxes',
            'translation_domain' => 'SiwappConfigBundle',
            'entry_type' => 'Siwapp\CoreBundle\Form\TaxType',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
        ));

        $builder->add('series', CollectionType::class, array(
            'label' => 'form.series',
            'translation_domain' => 'SiwappConfigBundle',
            'entry_type' => 'Siwapp\CoreBundle\Form\SeriesType',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
        ));
    }
}
