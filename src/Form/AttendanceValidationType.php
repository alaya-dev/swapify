<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['participantEvents'] as $participantEvent) {
            $builder->add('attended_' . $participantEvent->getId(), CheckboxType::class, [
                'label' => $participantEvent->getUser()->getPrenom() . ' ' . $participantEvent->getUser()->getNom(),
                'required' => false, // Checkbox is not required
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'participantEvents' => []
        ]);
    }
}
