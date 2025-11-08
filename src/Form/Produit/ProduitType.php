<?php

namespace App\Form\Produit;



use App\Entity\Unite\Unite;
use App\Entity\Produit\Produit;
use App\Form\Unite\UniteConversionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('code', TextType::class, ['required' => false])
            ->add('categorie')
            ->add('uniteDeBase', EntityType::class, [
                'class' => Unite::class,
                'choice_label' => 'nom',
                'label' => 'Unité de base',
            ])
            ->add('prixAchat', MoneyType::class, [
                'required' => false,
                'currency' => 'MGA', // ou EUR selon ton cas
            ])
            ->add('prixVente', MoneyType::class, [
                'required' => false,
                'currency' => 'MGA',
            ])
            ->add('stockInitial', NumberType::class)
            ->add('stockMinimum', NumberType::class)
            ->add('datePeremption')
            ->add('fournisseur')
            ->add('conversions', CollectionType::class, [
                'entry_type' => UniteConversionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Conversions d’unités',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
