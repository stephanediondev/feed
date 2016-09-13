<?php
namespace Readerself\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('link', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('website', TextType::class, [
            'constraints' => [
            ],
        ]);

        $builder->add('description', TextareaType::class, [
            'constraints' => [
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Readerself\CoreBundle\Entity\Feed',
            'csrf_protection' => false
        ]);
    }
}
