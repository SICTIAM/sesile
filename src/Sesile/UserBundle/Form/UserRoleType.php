<?php

namespace Sesile\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserRoleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userRoles', 'text', array('label' => 'Rôle utilisateur', 'attr' => array('class' => 'col-md-6'), 'label_attr' => array('class' => 'col-md-6')))
            ->add('user')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\UserRole'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_userbundle_userrole';
    }
}
