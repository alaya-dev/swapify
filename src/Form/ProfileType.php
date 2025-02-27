<?php
// src/Form/ProfileType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


    
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('tel', TextType::class)
            ->add('adresse', TextType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,  // Le mot de passe est optionnel
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['class' => 'form-control'],
                    'constraints' => [
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                            'groups' => ['update'], // Appliquer cette règle uniquement pendant la mise à jour
                        ])
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => ['class' => 'form-control']
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'mapped' => false,
            ])



            ->add('imageUrl', FileType::class, [
                'label' => 'Image de profil',
                'required' => false,  
                'multiple' => false,  
                'mapped' => false,
                'attr' => ['class' => 'input-field'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',  
                        'mimeTypes' => ['image/jpeg', 'image/png'],  
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG)',
                    ])
                ]
            ])
            
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => function (FormInterface $form) {
                // Si le mot de passe est vide, on ne fait pas la validation
                $user = $form->getData();
                if ($user && (null === $user->getPassword() || $user->getPassword() === '')) {
                    return ['Default']; // Validation standard sans validation du mot de passe
                }

                return ['Default', 'update']; // Validation du mot de passe pendant la mise à jour
            },
        ]);
    }
}
