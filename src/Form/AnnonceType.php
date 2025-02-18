<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\Image;
use App\Entity\position;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre',null, [
            'attr' => ['placeholder' => 'Entrez un titre'],
        ])
        ->add('description', TextareaType::class, [
            'attr' => [ 'placeholder' => 'Décrivez votre annonce'],
        ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'libelle',
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
            ])

            ->add('imageFile', FileType::class, [
                'label' => 'Upload Images',
                'mapped' => false, 
                'required' => false,
                'multiple' => true
            ])
            
                
            
            ->add('localisation_x', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('localisation_y', HiddenType::class, [
                'mapped' => false,
            ]);

        $builder->get('localisation_x')
        ->addModelTransformer(new CallbackTransformer(
            function ($originalValue) {
                return $originalValue;
            },
            function ($submittedValue) {
                return (float) $submittedValue;
              }
             )) ;

            $builder->get('localisation_y')
              ->addModelTransformer(new CallbackTransformer(
                function ($originalValue) {
                return $originalValue;
            },
            function ($submittedValue) {
                return (float) $submittedValue;
            }
        ))



        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }


      

}
