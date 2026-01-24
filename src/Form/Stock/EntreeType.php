<?php

namespace App\Form\Stock;

use App\Entity\Produit\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntreeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'label' => 'Produit *',
                'placeholder' => 'Sélectionnez un produit',
                'required' => true,
            ])
            ->add('numeroLot', TextType::class, [
                'required' => false,
                'label' => 'Numéro de lot',
            ])
            ->add('quantite', NumberType::class, [
                'required' => true,
                'label' => 'Quantité *',
                'attr' => [
                    'class' => 'js-numeric-input',
                ],
            ])
            ->add('datePeremption', DateType::class, [
                'required' => false,
                'label' => 'Date de péremption',
                'widget' => 'single_text',
            ])
            ->add('prixAchat', MoneyType::class, [
                'required' => false,
                'currency' => 'MGA',
                'grouping' => true,
                'label' => 'Prix d\'achat',
                'attr' => [
                    'class' => 'js-format-thousands',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // This form is not mapped to any entity
        ]);
    }
}
