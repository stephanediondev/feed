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
        $fields = [];
        $fields[] = 'endpoint';
        $fields[] = 'public_key';
        $fields[] = 'authentication_secret';
        $fields[] = 'content_encoding';

        foreach ($fields as $field) {
            switch ($field) {
                case 'endpoint':
                    $builder->add('endpoint', TextType::class, [
                        'label' => 'endpoint',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
                case 'public_key':
                    $builder->add('public_key', TextType::class, [
                        'label' => 'public_key',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'autocomplete' => 'nope',
                        ],
                    ]);
                    break;
                case 'authentication_secret':
                    $builder->add('authentication_secret', PasswordType::class, [
                        'label' => 'authentication_secret',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                        ],
                    ]);
                    break;
                case 'content_encoding':
                    $builder->add('content_encoding', TextType::class, [
                        'label' => 'content_encoding',
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                    break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PushModel::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'data';
    }
}
