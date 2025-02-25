<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objective')
            ->add('startHour', null, [
                'widget' => 'single_text'
            ])
            ->add('endHour', null, [
                'widget' => 'single_text'
            ])

            ->add('typeSession',ChoiceType::class,[
                'required' => true,
                'label' => 'type session',
                'choices' => [
                    'En ligne' => 'En ligne',
                    'Présentiel' => 'Présentiel']
            ])
            
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
