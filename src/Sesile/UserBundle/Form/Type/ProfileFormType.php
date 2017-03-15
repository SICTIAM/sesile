<?php

namespace Sesile\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
//use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            // Symfony 2.1 support with the old constraint class
            $constraint = new OldUserPassword();
        }*/

        $this->buildUserForm($builder, $options);

        $builder->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'required' => false,
            'first_options' => array('label' => 'Mot de passe'),
            'second_options' => array('label' => 'Confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        $builder->add('apiactivated', CheckboxType::class, array('label' => 'API', 'required' => false,))
            ->add('apitoken', TextType::class, array('attr' => array('read_only')))
            ->add('apisecret', TextType::class, array('attr' => array('read_only')));

        $builder->add('password', PasswordType::class, array('label' => 'Mot de passe actuel', 'required' => false));
    }

    public function getName()
    {
        return 'sesile_user_profile';
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Prenom', TextType::class, array('label' => 'Prénom', 'attr' => array('class' => 'pouet')))
            ->add('Nom', TextType::class, array('label' => ' Nom', 'attr' => array('class' => 'pouet')))
//            ->add('username', 'email', array('label' => 'Adresse E-mail', 'attr' => array('class' => 'pouet')))
            ->add('qualite', TextareaType::class, array('label' => 'Qualité', 'attr' => array('class' => 'pouet qualite', 'cols' => '37'), 'max_length' => 250, 'label_attr' => array('class' => 'label_form_textarea')))

            ->add('file', FileType::class, array('label' => 'Avatar',
                'data_class' => null,
                'required' => false,
                'attr' => array('class' => 'pouet')
            ))
            ->add('fileSignature', FileType::class, array('label' => 'Signature',
            'data_class' => null,
            'required' => false,
            'attr' => array('class' => 'pouet')
        ))
            ->add('submit', SubmitType::class, array('label' => 'Mettre à jour'))
        ;

    }

}