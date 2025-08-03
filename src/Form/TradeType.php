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

use App\Entity\Employee;
use App\Entity\Reference;
use App\Entity\Trade;
use App\Entity\TradeCategory;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TradeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'Name eingeben'
                ]
            ])
            ->add('lead', TextareaType::class, [
                'required' => true,
                'label' => 'Lead',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Lead eingeben'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false, // important: this have to be false! as this field will be hidden
                'attr' => [
                    'class' => 'ckeditor'
                ],
            ])
            ->add('tradeCategory', EntityType::class, [
                'label' => 'Gewerbe Kategorie',
                'class' => TradeCategory::class,
                'choice_label' => 'name',
            ])
            ->add('isMultiConsultant', CheckboxType::class, [
                'label'    => 'Berater ist für Basel und Zürich gleichzeitig zuständig',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trade::class,
        ]);
    }
}
