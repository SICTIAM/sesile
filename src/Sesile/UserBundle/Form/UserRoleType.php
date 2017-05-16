<?php

namespace Sesile\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userRoles', TextType::class, array(
                'translation_domain' => 'FOSUserBundle',
                'label' => 'profile.user_roles',
                'attr' => array('class' => 'col-md-5 col-sm-5 col-xs-5'),
                'label_attr' => array('class' => 'col-md-5 col-sm-5 col-xs-5')))
            ->add('user')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\UserRole'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sesile_userbundle_userrole';
    }
}
