<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("recaptcha", ReCaptchaType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez valider le reCAPTCHA.',
                ]),
            ],
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,  
        ]);
    }
}