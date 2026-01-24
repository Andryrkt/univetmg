<?php

namespace App\Form\Produit;

use App\Entity\Produit\Categorie;
use App\Entity\Unite\Unite;
use App\Entity\Produit\Produit;
use App\Form\Stock\LotType; // Added
use App\Form\Unite\ConditionnementType;
use App\Repository\Produit\CategorieRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Admin\Fournisseur;

class ProduitType extends AbstractType
{
    private CategorieRepository $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom *',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('categorieParent', EntityType::class, [
                'class' => Categorie::class,
                'choices' => $this->categorieRepository->findRootCategories(),
                'choice_label' => 'nom',
                'label' => 'Catégorie Principale',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                // Le champ est masqué et sera géré par JS
                'attr' => ['style' => 'display:none;'],
                'label_attr' => ['style' => 'display:none;'],
                'required' => false, // ou true selon votre logique
            ])
            ->add('uniteDeBase', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité de base',
            ])
            // Removed prixAchat field
            ->add('prixVente', MoneyType::class, [
                'required' => false,
                'currency' => 'MGA',
                'grouping' => true,
                'attr' => [
                    'class' => 'form-control js-format-thousands',
                ]
            ])
            // Removed stockInitial field
            ->add('stockMinimum', NumberType::class, [
                'required' => false,
                'label' => 'Stock minimum',
                'attr' => [
                    'class' => 'form-control js-numeric-input',
                ],
            ])
            // Removed datePeremption field
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nom',
                'label' => 'Fournisseur',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]   )
            ->add('conditionnements', CollectionType::class, [
                'entry_type' => ConditionnementType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Conditionnements (ex: boîte, carton)',
            ])
            ->add('lots', CollectionType::class, [ // Added
                'entry_type' => LotType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Lots du produit',
                'entry_options' => ['label' => false],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
