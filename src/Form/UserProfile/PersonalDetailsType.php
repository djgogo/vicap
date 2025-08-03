<?php

namespace App\Form\UserProfile;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PersonalDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
                'attr' => [
                    'autocomplete' => 'given-name',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
                'attr' => [
                    'autocomplete' => 'family-name',
                ],
            ])
            ->add('phone', TelType::class, [
                'label'    => 'Telefon',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label'           => 'E‑Mail',
                'invalid_message' => 'E‑Mail ist erforderlich.',
            ])
            ->add('jobDesignation', TextType::class, [
                'required' => true,
                'label'    => 'Berufsbezeichnung',
            ])
            ->add('birthdate', DateType::class, [
                'label'    => 'Geburtsdatum',
                'required' => false,
                'format'   => DateType::HTML5_FORMAT,
                'widget'   => 'single_text',
                'attr'     => [
                    'id'          => 'birthdateInput',
                ],
            ])
            ->add('company', TextType::class, [
                'label'    => 'Firma',
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'choices'  => $user->getAllUserRoles(),
                'mapped'   => false,
                'expanded' => false,
                'multiple' => false,
                'label'    => 'Benutzerrolle',
                'data'     => $user->getRoles()[0],
            ])
            // location
            ->add('address', TextareaType::class, [
                'label'    => 'Adresse',
                'required' => false,
            ])
            ->add('zip', null, [
                'label'    => 'PLZ',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label'    => 'Stadt',
                'required' => false,
            ])
            ->add('country', null, [
                'label'    => 'Land',
                'required' => false,
            ])
            ->add('about', TextareaType::class, [
                'label'    => 'Über mich',
                'required' => false,
            ])
            // authentication
            ->add('isEmailConfirmed', null, [
                'label'    => 'E‑Mail ist bestätigt',
                'required' => false,
            ])
            ->add('isActive', null, [
                'label'    => 'Benutzer aktiv',
                'required' => false,
            ])
            ->add('isSuperAdmin', null, [
                'label'    => 'Ist Super‑Administrator',
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
