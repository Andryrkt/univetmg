<?php

namespace App\Form\Vente;

use App\Entity\Vente\TypeClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TypeClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du type de client',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Client Fidèle, Grossiste'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length(['max' => 100])
                ]
            ])
            ->add('tauxRemise', NumberType::class, [
                'label' => 'Taux de remise (%)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le taux de remise est obligatoire']),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Le taux de remise doit être entre {{ min }}% et {{ max }}%'
                    ])
                ],
                'help' => 'Pourcentage de réduction accordé à ce type de client (0-100%)'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description optionnelle du type de client'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'help' => 'Décochez pour désactiver ce type de client'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeClient::class,
        ]);
    }
}
