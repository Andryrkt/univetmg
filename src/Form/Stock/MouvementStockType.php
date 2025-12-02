<?php

namespace App\Form\Stock;

use App\Entity\Produit\Produit;
use App\Entity\Stock\MouvementStock;
use App\Enum\TypeMouvement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'label' => 'Produit',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionner un produit',
            ])
            ->add('type', EnumType::class, [
                'class' => TypeMouvement::class,
                'choice_label' => fn(TypeMouvement $type) => $type->getLabel(),
                'label' => 'Type de mouvement',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0.01,
                    'step' => 0.01,
                ],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Raison du mouvement (optionnel)',
                ],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'N° de bon, facture, etc. (optionnel)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MouvementStock::class,
        ]);
    }
}
