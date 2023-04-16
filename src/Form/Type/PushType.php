<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\PushModel;
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
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('public_key', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('authentication_secret', PasswordType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('content_encoding', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PushModel::class,
            'csrf_protection' => false,
        ]);
    }
}
