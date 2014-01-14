<?php

namespace Sesile\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            // Symfony 2.1 support with the old constraint class
            $constraint = new OldUserPassword();
        }

        $this->buildUserForm($builder, $options);

        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'first_options' => array('label' => 'Mot de passe'),
            'second_options' => array('label' => 'Confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
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
            ->add('Prenom', null, array('label' => 'Prénom', 'attr' => array('class' => 'pouet')))
            ->add('Nom', null, array('label' => ' Nom', 'attr' => array('class' => 'pouet')))
            ->add('email', 'email', array('label' => 'Adresse e-mail', 'attr' => array('class' => 'pouet')));
    }

}