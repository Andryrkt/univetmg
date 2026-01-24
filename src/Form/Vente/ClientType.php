<?php

namespace App\Form\Vente;

use App\Entity\Vente\Client;
use App\Entity\Vente\TypeClient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du client',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Jean Dupont']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 034 00 000 00']
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('typeClient', EntityType::class, [
                'class' => TypeClient::class,
                'choice_label' => 'nom',
                'label' => 'Type de client',
                'required' => false,
                'placeholder' => 'Sélectionnez un type de client',
                'attr' => ['class' => 'form-select'],
                'help' => 'Le type de client détermine les remises applicables'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
