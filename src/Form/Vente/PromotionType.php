<?php

namespace App\Form\Vente;

use App\Entity\Produit\Produit;
use App\Entity\Vente\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la promotion',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Soldes d\'hiver 2026'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire'])
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire'])
                ]
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire']),
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[dateDebut].data',
                        'message' => 'La date de fin doit être après la date de début'
                    ])
                ]
            ])
            ->add('tauxRemise', NumberType::class, [
                'label' => 'Taux de remise (%)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 15',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01
                ],
                'constraints' => [
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Le taux de remise doit être entre {{ min }}% et {{ max }}%'
                    ])
                ],
                'help' => 'Pourcentage de réduction (laissez vide si vous utilisez un montant fixe)'
            ])
            ->add('montantRemise', MoneyType::class, [
                'label' => 'Montant de remise fixe',
                'required' => false,
                'currency' => 'MGA',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 5000'],
                'help' => 'Montant fixe de réduction (laissez vide si vous utilisez un pourcentage)'
            ])
            ->add('produits', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'form-select', 'size' => 10],
                'label' => 'Produits concernés',
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Vous devez sélectionner au moins un produit'
                    ])
                ],
                'help' => 'Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs produits'
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'help' => 'Décochez pour désactiver cette promotion'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'constraints' => [
                new Assert\Callback([$this, 'validateRemise'])
            ]
        ]);
    }

    /**
     * Validate that at least one of tauxRemise or montantRemise is set
     */
    public function validateRemise(Promotion $promotion, ExecutionContextInterface $context): void
    {
        if ($promotion->getTauxRemise() === null && $promotion->getMontantRemise() === null) {
            $context->buildViolation('Vous devez définir soit un taux de remise, soit un montant de remise')
                ->atPath('tauxRemise')
                ->addViolation();
        }

        if ($promotion->getTauxRemise() !== null && $promotion->getMontantRemise() !== null) {
            $context->buildViolation('Vous ne pouvez pas définir à la fois un taux et un montant de remise. Choisissez l\'un ou l\'autre.')
                ->atPath('tauxRemise')
                ->addViolation();
        }
    }
}
