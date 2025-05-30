<?php

namespace App\Form;

use App\Entity\Running;
use App\Entity\RunningParticipant;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RunningParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dorsal', IntegerType::class, [
                'label' => 'Dorsal',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1
                ]
            ])
            ->add('banned')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('running', EntityType::class, [
                'class' => Running::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RunningParticipant::class,
        ]);
    }
}
