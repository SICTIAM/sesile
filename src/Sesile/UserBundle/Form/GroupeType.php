<?php

namespace Sesile\UserBundle\Form;

use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupeType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('nom')
//            ->add('collectivite')
//            ->add('couleur')
//            ->add('json')
            ->add('types', 'entity', array(
                'class' => 'SesileClasseurBundle:TypeClasseur',
                'property' => 'nom',
                'multiple' => true,
                'expanded' => true
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\Groupe'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_userbundle_groupe';
    }
}
