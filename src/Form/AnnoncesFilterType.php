<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnoncesFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, ['required' => false])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('Region', ChoiceType::class, [
                'required' => true,
                'label' => 'Région',
                'choices' => [
                    'Tunis' => 'Tunis',
                    'Ariana' => 'Ariana',
                    'Ben Arous' => 'Ben Arous',
                    'Manouba' => 'Manouba',
                    'Sousse' => 'Sousse',
                    'Sfax' => 'Sfax',
                    'Nabeul' => 'Nabeul',
                    'Kairouan' => 'Kairouan',
                    'Monastir' => 'Monastir',
                    'Kasserine' => 'Kasserine',
                    'Gabès' => 'Gabès',
                    'Medenine' => 'Medenine',
                    'Tozeur' => 'Tozeur',
                    'Béja' => 'Béja',
                    'Jendouba' => 'Jendouba',
                    'Le Kef' => 'Le Kef',
                    'Siliana' => 'Siliana',
                    'Mahdia' => 'Mahdia',
                    'Zaghouan' => 'Zaghouan',
                    'Tataouine' => 'Tataouine',
                    'Gafsa' => 'Gafsa',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Nefta' => 'Nefta',
                    'Douz' => 'Douz',
                ],
                'placeholder' => 'Sélectionnez une région', 
            ])
            ->add('dateCreation', ChoiceType::class, [
                'required' => true, 
                'label' => 'Postuler',
                'mapped' => false,
                'choices' => [
                    'Aujourd\'hui' => 'today',
                    'Il y a 7 jours' => 'last_week',
                    'Il y a 1 mois' => 'last_month',
                    'Plus d\'un mois' => 'older',
                ],
                'placeholder' => 'Sélectionnez une période', 
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
