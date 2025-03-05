<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;




class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom', TextType::class, [
                'label' => 'Nom *',
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre nom']
            ])

            ->add('prenom', TextType::class, [
                'label' => 'Prénom *',
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre prénom']
            ])

            ->add('email', EmailType::class, [
                'label' => 'E-mail *',
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre email']
            ])


            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre date de naissance'],
                'label' => 'Date de naissance *',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "La date de naissance ne peut pas être vide."
                    ]),
                    new Assert\Callback([$this, 'validateAge'])  // Ajout de la contrainte de validation personnalisée
                ]
            ])


            ->add('tel', TextType::class, [
                'label' => 'Téléphone *',
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre numéro de téléphone']
            ])

            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'input-field', 'placeholder' => 'Votre adresse'],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe *',
                    'attr' => ['class' => 'input-field', 'placeholder' => '***********']
                ],
                'second_options' => [
                    'label' => 'Confirmer votre mot de passe *',
                    'attr' => ['class' => 'input-field', 'placeholder' => '***********']
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
            ])
            ->add('consent', CheckboxType::class, [
                'mapped' => false,
                'label' => "En m'inscrivant, j'accepte les conditions d'utilisation",
                'label_attr' => ['style' => 'margin-right: 10px;'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\IsTrue([
                        'message' => 'Vous devez accepter les conditions.'
                    ])
                ]
            ])
            ->add('imageUrl', FileType::class, [
                'label' => 'Image de profil (optionnel)',
                'required' => false,  
                'multiple' => false,  
                'attr' => ['class' => 'input-field'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',  
                        'mimeTypes' => ['image/jpeg', 'image/png'],  
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG)',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }


    // Méthode de validation personnalisée pour vérifier l'âge
    public function validateAge($dateNaissance, ExecutionContextInterface $context)
    {
        // Si la date est vide (ce qui ne devrait pas arriver à cause de NotBlank)
        if (null === $dateNaissance) {
            return;
        }

        // Assurez-vous que la date est bien un objet DateTime
        if (!$dateNaissance instanceof \DateTime) {
            $dateNaissance = \DateTime::createFromFormat('Y-m-d', $dateNaissance);
        }

        // Calculez l'âge
        $today = new \DateTime();
        $age = $today->diff($dateNaissance)->y;

        // Vérifiez que l'âge est d'au moins 13 ans
        if ($age < 13) {
            $context->buildViolation('Vous devez avoir au moins 13 ans.')
                ->atPath('dateNaissance')
                ->addViolation();
        }
    }
}