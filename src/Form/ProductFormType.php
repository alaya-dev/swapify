<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Souk;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price', NumberType::class, [
                'label' => 'Price',
                'required' => true,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
                'scale' => 2,
            ])
            ->add('discoutPrice', NumberType::class, [
                'label' => 'Discount Price',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'scale' => 2,
            ])
            ->add('image', FileType::class, [
                'label' => 'Product Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, or WebP).',
                    ]),
                ],
            ])
            ->add('souk', EntityType::class, [
                'class' => Souk::class,
                'choices' => $options['souks'],
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'souks' => [],
        ]);
    }
}
