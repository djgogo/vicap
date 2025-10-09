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

use App\Entity\Blog;
use App\Entity\BlogCategory;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Determine default selected author: prefer entity's current author,
        // else fall back to provided current_user option (if any)
        $blog = $builder->getData();
        $defaultAuthor = null;
        if ($blog instanceof Blog && $blog->getAuthor()) {
            $defaultAuthor = $blog->getAuthor();
        } elseif (!empty($options['current_user']) && $options['current_user'] instanceof User) {
            $defaultAuthor = $options['current_user'];
        }

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'enter title'
                ]
            ])
            ->add('author', EntityType::class, [
                'label' => 'Author',
                'class' => User::class,
                'choice_label' => 'displayName',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                // Preselect current user if author not already set on entity
                'data' => $defaultAuthor,
                'attr' => [
                    'class' => 'js-choices'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Description',
                'required' => false, // important: this have to be false! as this field will be hidden
                'attr' => [
                    'class' => 'ckeditor'
                ],
            ])
            ->add('blogCategories', EntityType::class, [
                'label' => 'Blog Categories',
                'class' => BlogCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'js-choices'
                ]
            ])
        ;

        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'label' => 'Blog Image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
            'submit_label' => 'Create Post', // Default label
            'current_user' => null, // allows prefilling author select
        ]);

        $resolver->setAllowedTypes('current_user', ['null', User::class]);
    }
}
