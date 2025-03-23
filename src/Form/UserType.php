<?php

// src/Form/UserType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $step = $options['step'] ?? 1;

        if ($step === 1) {
            $builder
                ->add('firstName', TextType::class, [
                    'attr' => [
                        'class' => 'form-input',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'validation_groups' => ['step1']
                ])
                ->add('lastName', TextType::class, [
                    'attr' => [
                        'class' => 'form-input',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'validation_groups' => ['step1']
                ]);
        } else {
            $builder
                ->add('email', EmailType::class, [
                    'attr' => [
                        'class' => 'form-input',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'validation_groups' => ['step2']
                ])
                ->add('password', PasswordType::class, [
                    'attr' => [
                        'class' => 'form-input',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'validation_groups' => ['step2']
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'step' => 1,
            'validation_groups' => ['Default']
        ]);
    }
}

