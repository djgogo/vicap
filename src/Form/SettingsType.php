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

use App\Entity\AdminOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adminEmail', EmailType::class, [
                'required' => true,
                'invalid_message' => 'error.value_should_not_be_empty',
                'label' => new TranslatableMessage('label.admin_email'),
                'attr' => [
                    'placeholder' => new TranslatableMessage('text.enter_email')
                ]
            ])
            ->add('salesEmail', EmailType::class, [
                'required' => true,
                'invalid_message' => 'error.value_should_not_be_empty',
                'label' => new TranslatableMessage('label.sales_email'),
                'attr' => [
                    'placeholder' => new TranslatableMessage('text.enter_email')
                ]
            ])
            ->add('companyName', TextType::class, [
                'required' => true,
                'invalid_message' => new TranslatableMessage('error.value_should_not_be_empty'),
                'label' => new TranslatableMessage('label.company_name'),
                'attr' => [
                    'placeholder' => new TranslatableMessage('text.enter_company_name')
                ]
            ])
            ->add('companyAddress', TextareaType::class, [
                'required' => true,
                'invalid_message' => new TranslatableMessage('error.value_should_not_be_empty'),
                'label' => new TranslatableMessage('label.company_address'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => new TranslatableMessage('text.enter_company_address')
                ]
            ])
            ->add('companyPhone', TelType::class, [
                'required' => true,
                'invalid_message' => new TranslatableMessage('error.value_should_not_be_empty'),
                'label' => new TranslatableMessage('label.company_phone'),
                'attr' => [
                    'placeholder' => new TranslatableMessage('text.enter_company_phone')
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdminOption::class,
        ]);
    }
}
