<?php

namespace App\Form\Unite;

use App\Entity\Unite\ConversionStandard;
use App\Entity\Unite\Unite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConversionStandardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uniteOrigine', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité d\'origine',
                'placeholder' => 'Choisir une unité',
            ])
            ->add('uniteCible', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité cible',
                'placeholder' => 'Choisir une unité',
            ])
            ->add('facteur', NumberType::class, [
                'label' => 'Facteur de conversion (1 Origine = ? Cible)',
                'help' => 'Exemple: Si 1 Kilogramme = 1000 Grammes, le facteur est 1000.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConversionStandard::class,
        ]);
    }
}
