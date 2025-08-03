<?php

declare(strict_types=1);

namespace App\Form\TermTemplates;

use App\DTO\Term\TermTemplateTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermTemplateTranslationType extends AbstractType
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
            ->add('content', TextareaType::class, [
                'label' => 'label.content',
                'required' => false, // important: this have to be false! as this field will be hidden
                'attr' => [
                    'class' => 'ckeditor'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TermTemplateTranslation::class,
        ]);
    }
}
