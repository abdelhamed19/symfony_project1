<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Name should not be blank',
                    ]),
                    new NotNull([
                        'message' => 'Name should not be null',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'csrf_protection' => false
        ]);
    }
}
