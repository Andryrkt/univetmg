<?php

namespace App\Form\Vente;

use App\Entity\Vente\Client;
use App\Entity\Vente\Vente;
use App\Enum\StatutVente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateVente', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de vente',
                'attr' => ['class' => 'form-control'],
                'input' => 'datetime_immutable',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom', // Or __toString
                'label' => 'Client',
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionner un client',
                'required' => false,
            ])
            ->add('statut', EnumType::class, [
                'class' => StatutVente::class,
                'label' => 'Statut',
                'attr' => ['class' => 'form-select'],
                'choice_label' => fn (StatutVente $choice) => $choice->label(),
            ])
            ->add('ligneVentes', CollectionType::class, [
                'entry_type' => LigneVenteType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
        ]);
    }
}
