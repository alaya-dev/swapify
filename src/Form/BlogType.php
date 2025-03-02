<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Titre')
        ->add('Contenu', TextareaType::class, [
            'label' => 'Contenu', 
            'required' => true,   
            'attr' => [
                'class' => 'form-control', 
                'rows' => 5,              
                'placeholder' => 'Entrez le contenu ici...', 
            ],
        ])
        ->add('imageFile', FileType::class, [
            'label' => 'Blog Image',
            'required' => false,
            'mapped' => false, // This field is not mapped to the entity
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
