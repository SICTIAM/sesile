<?php

namespace Sesile\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;


class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'email', array('label' => 'Email'))
            ->add('email', 'hidden')
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'options' => array('translation_domain' => 'FOSUserBundle', 'always_empty' => 'true'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('Nom', 'text')
            ->add('Prenom', 'text')
            ->add('ville', 'text', array('required' => false,))
            ->add('cp', 'text', array('label' => 'Code Postal', 'required' => false,))
            ->add('departement', 'text', array('label' => 'Département', 'required' => false,))
            ->add('pays', 'text', array('required' => false,))
            ->add('role', 'text', array('label' => 'Rôle', 'required' => false,))
            ->add('enabled', null, array('label' => 'Activé'))
            ->add('apiactivated', 'checkbox', array('label' => 'API', 'required' => false,))
            ->add('apitoken', 'text', array('attr' => array('disabled' => true)))
            ->add('apisecret', 'text', array('attr' => array('disabled' => true)))
            ->add('file', 'file', array('label' => 'Avatar',
                'data_class' => null,
                'required' => false,
            ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_userbundle_user';
    }
}
