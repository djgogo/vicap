<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = new User();

        $builder
            ->add('email', null, [
                'label' => 'E-Mail',
                'attr' => [
                    'placeholder' => 'E-Mail eingeben',
                    'autocomplete' => 'email'
                ]
            ])
            ->add('firstName', null, [
                'label' => 'Vorname',
                'attr' => [
                    'placeholder' => 'Vorname eingeben',
                    'autocomplete' => 'given-name'
                ]
            ])
            ->add('lastName', null, [
                'label' => 'Nachname',
                'attr' => [
                    'placeholder' => 'Nachname eingeben',
                    'autocomplete' => 'family-name'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $user->getAdminIndexUserRoles(),
                'mapped' => false, // Assuming you handle roles assignment manually
                'expanded' => false, // Dropdown
                'multiple' => false, // Allow multiple selections
                'label' => 'Benutzerrolle',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Passwort',
                    'attr' => [
                        'class' => 'pe-5 password-input',
                        'onpaste' => 'return false',
                        'aria-describedby' => 'passwordInput',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                        'placeholder' => 'Passwort eingeben',
                        'autocomplete' => 'new-password'
                    ]],
                'second_options' => [
                    'label' => 'Passwort bestätigen',
                    'attr' => [
                        'class' => 'pe-5 password-input',
                        'onpaste' => 'return false',
                        'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                        'placeholder' => 'Passwort bestätigen',
                        'autocomplete' => 'new-password'
                    ]],
                'invalid_message' => 'Passworte nicht identisch.',
            ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'button.create_user',
            'attr' => [
                'class' => 'btn btn-success w-100'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => [
                'registration'
            ],
            'data_class' => User::class,
        ]);
    }
}
