<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Portfolio;
use App\Entity\PortfolioCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'enter name'
                ]
            ])
            ->add('client', TextType::class, [
                'required' => true,
                'label' => 'Client',
                'attr' => [
                    'placeholder' => 'enter client'
                ]
            ])
            ->add('websiteUrl', TextType::class, [
                'required' => true,
                'label' => 'Website Url',
                'attr' => [
                    'placeholder' => 'enter website url'
                ]
            ])
            ->add('features', TextareaType::class, [
                'required' => true,
                'label' => 'Features',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'enter features'
                ]
            ])
            ->add('technologies', TextareaType::class, [
                'required' => true,
                'label' => 'Technologies',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'enter technologies'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false, // important: this have to be false! as this field will be hidden
                'attr' => [
                    'class' => 'ckeditor'
                ],
            ])
            ->add('portfolioCategory', EntityType::class, [
                'label' => 'Portfolio Category',
                'class' => PortfolioCategory::class,
                'choice_label' => 'name',
            ])
        ;

        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => !$options['edit'],
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'label' => 'Frontpage Image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Portfolio::class,
            'submit_label' => 'Create Portfolio', // Default label
            'edit' => false
        ]);
    }
}
