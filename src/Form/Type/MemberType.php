<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ]);

        if (Request::METHOD_POST === $options['request_method']) {
            $builder->add('plainPassword', PasswordType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }

        if (Request::METHOD_PUT === $options['request_method']) {
            $builder->add('plainPassword', PasswordType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
            'csrf_protection' => false,
            'request_method' => null,
        ]);
    }
}
