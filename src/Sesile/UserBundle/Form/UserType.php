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
            ->add('username', 'email', array('label' => 'Email', 'label_attr' => array('class' => 'sesile_userbundle_user_username_label')))
            ->add('email', 'hidden')
            ->add('Nom', 'text')
            ->add('Prenom', 'text', array('label' => 'Prénom'))
            ->add('ville', 'text', array('required' => false,))
            ->add('cp', 'text', array('label' => 'Code Postal', 'required' => false,))
            ->add('departement', 'text', array('label' => 'Département', 'required' => false,))
            ->add('pays', 'text', array('required' => false,))
            //->add('role', 'text', array('label' => 'Rôle', 'required' => true))
            ->add('userRole', 'collection', array(
                'label' => '',
                'type' => new UserRoleType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'options' => array('attr' => array('class' => ''))))
//                ))
            ->add('qualite', 'textarea', array('label' => 'Qualité', 'required' => false, 'max_length' => 250, 'attr' => array('cols' => '37', 'class' => 'qualite'), 'label_attr' => array('class' => 'label_form_textarea')))
            ->add('enabled', null, array('label' => 'Activé', 'required' => false))
            ->add('apiactivated', 'checkbox', array('label' => 'API', 'required' => false))
            ->add('apitoken', 'text', array('read_only' => true))
            ->add('apisecret', 'text', array('read_only' => true))
            ->add('file', 'file', array('label' => 'Avatar',
                'data_class' => null,
                'required' => false,
            ))
            ->add('fileSignature', 'file', array('label' => 'Signature',
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
