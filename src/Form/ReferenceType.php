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

use App\Entity\Reference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Namen eingeben'
                ]
            ])
            ->add('url', TextType::class, [
                'required' => true,
                'label' => 'URL',
                'attr' => [
                    'placeholder' => 'URL eingeben'
                ]
            ])
        ;

        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => !$options['edit'],
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'label' => 'Logo',
            ]);

        // submit buttons
        $builder->add('submit', SubmitType::class, [
            'label' => $options['submit_label'],
            'attr' => [
                'class' => 'btn btn-primary btn'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reference::class,
            'submit_label' => 'Referenz erstellen', // Default label
            'edit' => false
        ]);
    }
}
