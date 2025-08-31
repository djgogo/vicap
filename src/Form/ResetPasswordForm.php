<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identity', null, [
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'placeholder' => 'Email',
                    'autocomplete' => 'email'
                ],
                'constraints' => [
                    new NotBlank(),

                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Reset',
                'attr' => [
                    'class' => 'rr-primary-btn w-100 text-center'
                ]
            ]);
    }

}
