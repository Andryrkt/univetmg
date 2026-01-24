<?php

namespace App\Form\Stock;

use App\Entity\Stock\Lot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class LotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroLot', TextType::class, [
                'required' => false,
                'label' => 'Numéro de lot',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('quantite', NumberType::class, [
                'required' => true,
                'label' => 'Quantité *',
                'attr' => [
                    'class' => 'form-control js-numeric-input',
                ],
            ])
            ->add('datePeremption', DateType::class, [
                'required' => false,
                'label' => 'Date de péremption',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('prixAchat', MoneyType::class, [
                'required' => false,
                'currency' => 'MGA',
                'grouping' => true,
                'label' => 'Prix d\'achat *',
                'attr' => [
                    'class' => 'form-control js-format-thousands',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lot::class,
        ]);
    }
}
