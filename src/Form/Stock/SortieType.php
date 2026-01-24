<?php

namespace App\Form\Stock;

use App\Entity\Produit\Produit;
use App\Entity\Stock\Lot;
use App\Repository\Stock\LotRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
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
                'attr' => ['class' => 'form-control js-produit-select tom-select'],
            ])
            ->add('lot', EntityType::class, [
                'class' => Lot::class,
                'choice_label' => function (Lot $lot) {
                    return sprintf('%s (Stock: %s)', $lot->getNumeroLot() ?: 'Sans numéro', $lot->getQuantite());
                },
                'label' => 'Lot *',
                'placeholder' => 'Sélectionnez un lot',
                'required' => true,
                'attr' => ['class' => 'form-control js-lot-select'],
                'choices' => [],
            ])
            ->add('quantite', NumberType::class, [
                'required' => true,
                'label' => 'Quantité à sortir *',
                'attr' => [
                    'class' => 'form-control js-numeric-input',
                    'min' => 0.01,
                ],
            ])
            ->add('motif', TextareaType::class, [
                'required' => false,
                'label' => 'Motif / Justification',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 2,
                ],
            ]);

        $formModifier = function (FormInterface $form, Produit $produit = null) {
            $choices = $produit ? $produit->getLots() : [];

            $form->add('lot', EntityType::class, [
                'class' => Lot::class,
                'choice_label' => function (Lot $lot) {
                    return sprintf('%s (Stock: %s)', $lot->getNumeroLot() ?: 'Sans numéro', $lot->getQuantite());
                },
                'label' => 'Lot *',
                'placeholder' => 'Sélectionnez un lot',
                'required' => true,
                'attr' => ['class' => 'form-control js-lot-select'],
                'choices' => $choices,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data['produit'] ?? null);
            }
        );

        $builder->get('produit')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $produit = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $produit);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Not mapped to an entity directly as it handles multiple logic
        ]);
    }
}
