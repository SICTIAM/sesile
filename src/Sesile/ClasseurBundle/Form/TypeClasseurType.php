<?php

namespace Sesile\ClasseurBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TypeClasseurType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text')
            ->add('Enregister', 'submit')
            //->add('template', null)
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\ClasseurBundle\Entity\TypeClasseur'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_classeurbundle_typeClasseur';
    }
}
