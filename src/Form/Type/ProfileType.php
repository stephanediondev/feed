<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\ProfileModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ]);

        $builder->add('password', PasswordType::class, [
            'required' => false,
        ]);

        $builder->add('passwordConfirm', PasswordType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfileModel::class,
            'csrf_protection' => false,
        ]);
    }
}
