<?php

namespace Sesile\ClasseurBundle\Form;

use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\UserBundle\Form\EtapeClasseurType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseurType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('validation', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm'
            ])
            ->add('description')
            /*->add('actions', CollectionType::class, array(
                'entry_type' => ActionType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'by_reference' => false
            ))*/
            ->add('visibilite')
            ->add('etapeClasseurs', CollectionType::class, array(
                'entry_type' => EtapeClasseurType::class,
                'allow_delete' => true,
                'allow_add' => true,
                'by_reference' => false
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Classeur::class,
            'csrf_protection' => false,
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_classeurbundle_classeur';
    }

}
