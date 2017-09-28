<?php

namespace Sesile\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('_nom')
            ->add('_prenom')
            ->add('collectivite')
            ->add('qualite')
            ->add('cp')
            ->add('ville')
            ->add('departement')
            ->add('pays')
            ->add('roles')
            ->add('enabled')
            ->add('apiactivated')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\User',
            'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sesile_userbundle_user';
    }


}
