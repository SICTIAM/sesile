<?php

namespace Sesile\ClasseurBundle\Form;

use Sesile\DocumentBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ClasseurPostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('actions')
            ->add('visibilite')
            ->add('user')
            ->add('type')
            ->add('circuit_id')
            ->add('documents', CollectionType::class, array(
                'entry_type'    => DocumentType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false
            ))
            ->add('copy',EntityType::class, array(
                'class' => 'SesileUserBundle:User',
                'multiple' => true
            ))
        ;
    }

    public function getParent()
    {
        return ClasseurType::class;
    }

}
