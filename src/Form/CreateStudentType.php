<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateStudentType extends AbstractType
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
            ->add('name', TextType::class)
            ->add('gender', null)
            ->add('image', FileType::class, [
                'mapped' => true,
                'required' => false,
                'constraints' => $fileConstraints,
            ])
            ->add('courses', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
            'csrf_protection' => false,
            'file_required' => true
        ]);
    }
}
