<?php

namespace App\Form;

use App\Entity\Livraison;
use App\Entity\Livreur;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('localisation_expediteur_lat')
            ->add('localisation_expediteur_lng')
            ->add('localisation_destinataire_lat')
            ->add('localisation_destinataire_lng')
            ->add('payment_exp')
            ->add('payment_dist')
            ->add('TelephoneExpediteur')
            ->add('CodePostalExpediteur')
            ->add('TelephoneDestinataire')
            ->add('CodePostalDestinataire')
            ->add('livreur', EntityType::class, [
                'class' => Livreur::class,
                'choice_label' => 'id',
            ])
           
            ->add('id_distinataire', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
        ]);
    }
}
