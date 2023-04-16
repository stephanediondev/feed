<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PushType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('endpoint', TextType::class, [
            'property_path' => 'token',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('publicKey', TextType::class, [
            'property_path' => 'extra_fields[public_key]',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('authenticationSecret', PasswordType::class, [
            'property_path' => 'extra_fields[authentication_secret]',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('contentEncoding', TextType::class, [
            'property_path' => 'extra_fields[content_encoding]',
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Connection::class,
            'csrf_protection' => false,
        ]);
    }
}
