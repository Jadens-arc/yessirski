<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('path')
            ->add('created', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('artists', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Artist::class,
                    'choice_label' => 'name',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,                  // Enables the prototype feature
                'prototype_name' => '__name__',       // The placeholder for new artist dropdowns
                'mapped' => false,
            ])
            ->add("save", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }
}
