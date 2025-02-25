<?php

namespace App\Form;

use App\Entity\Souk;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoukFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = (new \DateTime())->format('Y-m-d');
        $builder
            ->add('name')
            ->add('startSouke', null, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => $today,
                ],
            ])
            ->add('endSouke', null, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => $today,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Souk::class,
        ]);
    }
}
