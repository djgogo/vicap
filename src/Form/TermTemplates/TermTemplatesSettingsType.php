<?php

declare(strict_types=1);

namespace App\Form\TermTemplates;

use App\DTO\Term\TermTemplatesSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermTemplatesSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('groups', CollectionType::class, [
            'entry_type' => TermTemplateGroupType::class,
            'entry_options' => [
                'label' => false
            ],
            'allow_add' => false, // Disable adding new groups dynamically
            'allow_delete' => false, // Disable deleting groups dynamically
            'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TermTemplatesSettings::class,
        ]);
    }
}
