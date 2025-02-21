<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objet', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Objet de la réclamation',
                ],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Content',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Contenu...',
                    'rows' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class, 
        ]);
    }
}