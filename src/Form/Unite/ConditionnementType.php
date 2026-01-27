<?php

namespace App\Form\Unite;

use App\Entity\Unite\Conditionnement;
use App\Entity\Unite\Unite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConditionnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unite', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité de conditionnement',
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité contenue (en unité de base)',
            ])
            ->add('prixVente', MoneyType::class, [
                'label' => 'Prix du conditionnement (optionnel)',
                'required' => false,
                'currency' => 'MGA',
                'help' => 'Prix de vente spécifique pour ce conditionnement. Laissez vide pour utiliser le prix du produit.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conditionnement::class,
        ]);
    }
}
