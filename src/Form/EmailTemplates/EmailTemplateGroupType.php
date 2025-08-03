<?php

declare(strict_types=1);

namespace App\Form\EmailTemplates;

use App\DTO\Email\EmailTemplateGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Template Name',
                'disabled' => true, // Make name non-editable as it's a key
                'attr' => ['class' => 'form-control-plaintext'],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => EmailTemplateTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplateGroup::class,
        ]);
    }
}
