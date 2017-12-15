<?php

namespace Sesile\UserBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtapeClasseurType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ordre')
            ->add('users',EntityType::class, array(
                'class' => 'SesileUserBundle:User',
                'multiple' => true
            ))
            ->add('user_packs', EntityType::class, array(
                'class' => 'SesileUserBundle:UserPack',
                'multiple' => true
            ))
            //->add('etapeValidante')
            //->add('etapeValide')
            //->add('classeur')
            //->add('userValidant')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\EtapeClasseur'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sesile_userbundle_etapeclasseur';
    }


}
