<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fileConstraints = [
            new Assert\File([
                'maxSize' => '5M',
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                'mimeTypesMessage' => 'Please upload a valid image (JPEG or PNG)',
            ])
        ];

        if ($options['file_required']) {
            $fileConstraints[] = new Assert\NotBlank([
                'message' => 'Please upload an image file',
            ]);
        }

        $builder
            ->add('title', TextType::class)
            ->add('image', FileType::class, [
                'mapped' => false,
                'constraints' => $fileConstraints,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_value' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'csrf_protection' => false,
            'file_required' => false,
        ]);
    }
}
