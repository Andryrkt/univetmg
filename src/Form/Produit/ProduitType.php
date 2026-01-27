<?php

namespace App\Form\Produit;

use App\Entity\Unite\Unite;
use App\Entity\Produit\Produit;
use App\Entity\Admin\Fournisseur;
use App\Entity\Produit\Categorie;
use Doctrine\ORM\EntityRepository;
use App\Form\Stock\LotType; // Added
use App\Form\Unite\ConditionnementType;
use Symfony\Component\Form\AbstractType;
use App\Repository\Produit\CategorieRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;

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
                'attr' => [
                    'class' => 'form-control tom-select',
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                },
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                // Le champ est masqué et sera géré par JS
                'attr' => [
                    'style' => 'display:none;',
                    'class' => 'tom-select'
                ],
                'label_attr' => ['style' => 'display:none;'],
                'required' => false, // ou true selon votre logique
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ])
            ->add('uniteDeBase', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité de base',
                'attr' => [
                    'class' => 'form-control tom-select',
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                },
            ])
            // Removed prixAchat field
            ->add('prixVente', MoneyType::class, [
                'required' => false,
                'label' => 'Prix de vente (Unité de base)',
                'currency' => 'MGA',
                'grouping' => true,
                'help' => 'Prix pour une unité (ex: 1 comprimé, 1 ml)',
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
                    'class' => 'form-control tom-select',
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
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Vous devez ajouter au moins un lot pour définir le stock initial et le prix d\'achat.',
                    ]),
                    new Assert\Valid(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
