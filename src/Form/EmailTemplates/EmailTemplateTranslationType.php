<?php

declare(strict_types=1);

namespace App\Form\EmailTemplates;

use App\DTO\Email\EmailTemplateTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => [
                    'de' => 'de',
                    'en' => 'en',
                    'fr' => 'fr',
                    'it' => 'it',
                ],
                'label' => 'Locale',
                'disabled' => true, // Make locale non-editable
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplateTranslation::class,
        ]);
    }
}
