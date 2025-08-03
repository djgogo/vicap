<?php

namespace App\Form\Type;


use App\Entity\User\LocationTrait;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserLocationType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('country', null, [
            'label' => 'Land',
            'required' => false
        ]);
        $builder->add('city', null, [
            'label' => 'Stadt',
            'required' => false
        ]);
        $builder->add('address', TextareaType::class, [
            'label' => 'Adresse',
            'required' => false
        ]);
        $builder->add('zip', null, [
            'label' => 'PLZ',
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocationTrait::class
        ]);
    }
}