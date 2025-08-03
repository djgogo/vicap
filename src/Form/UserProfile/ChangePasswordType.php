<?php

namespace App\Form\UserProfile;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'required' => false,
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Passworte nicht identisch.',
                'first_options' => [
                    'label' => 'Passwort',
                    'attr' => [
                        'class' => 'pe-5 password-input',
                        'onpaste' => 'return false',
                        'placeholder' => 'Passwort eingeben',
                        'aria-describedby' => 'passwordInput',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    ],
                    'help' => 'Muss mindestens 8 Zeichen lang sein.',
                ],
                'second_options' => [
                    'label' => 'Passwort bestätigen',
                    'attr' => [
                        'class' => 'pe-5 password-input',
                        'onpaste' => 'return false',
                        'placeholder' => 'Neues Passwort bestätigen',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    ]
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
