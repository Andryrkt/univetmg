<?php

namespace App\Form\Vente;

use App\Entity\Produit\Produit;
use App\Entity\Unite\Unite;
use App\Entity\Vente\LigneVente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'choice_attr' => function(?Produit $produit) {
                    return $produit ? ['data-prix' => $produit->getPrixVente()] : [];
                },
                'attr' => ['class' => 'form-select produit-select'],
                'label' => false,
            ])
            ->add('unite', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => false,
                'attr' => ['class' => 'form-select unite-select'],
                // Important: The choices will be dynamically modified by JS. 
                // But Symfony needs valid choices for validation if we submit.
                // For now, let's list all units. It's safe enough for this scale.
                // Later optimization: use events to restrict based on product.
            ])
            ->add('quantite', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control quantite-input', 'min' => 0, 'step' => 0.01],
            ])
            ->add('prixUnitaire', MoneyType::class, [
                'currency' => 'MGA',
                'label' => false,
                'attr' => ['class' => 'form-control prix-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneVente::class,
        ]);
    }
}
