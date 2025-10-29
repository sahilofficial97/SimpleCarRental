<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'row_attr' => [
                    'class' => 'mb-5',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-slate-700 mb-1',
                ],
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border border-slate-300 px-4 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500',
                    'placeholder' => 'you@example.com',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Password',
                    'row_attr' => [
                        'class' => 'mb-5',
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm font-medium text-slate-700 mb-1',
                    ],
                    'attr' => [
                        'class' => 'mt-1 block w-full rounded-md border border-slate-300 px-4 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500',
                        'placeholder' => 'Your password',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'row_attr' => [
                        'class' => 'mb-6',
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm font-medium text-slate-700 mb-1',
                    ],
                    'attr' => [
                        'class' => 'mt-1 block w-full rounded-md border border-slate-300 px-4 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500',
                        'placeholder' => 'Re-enter password',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 6, max: 4096),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
