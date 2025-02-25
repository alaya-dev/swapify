<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use VictorPrdh\RecaptchaBundle\Validator\Constraints\IsValidCaptcha;



class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("recaptcha", ReCaptchaType::class, [
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez valider le reCAPTCHA.',
                ]),
                new IsValidCaptcha([
                    'message' => 'Le reCAPTCHA n\'est pas valide.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          //  'data_class' => null,  
        ]);
    }
}