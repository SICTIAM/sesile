<?php

namespace Sesile\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CollectiviteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('active')
            ->add('file')
            ->add('textmailnew')
            ->add('textmailrefuse')
            ->add('textmailwalid')
            ->add('textcopymailnew')
            ->add('textcopymailwalid')
            ->add('message')
            ->add('abscisses_visa')
            ->add('ordonnees_visa')
            ->add('abscisses_signature')
            ->add('ordonnees_signature')
            ->add('couleur_visa')
            ->add('titre_visa')
            ->add('page_signature')
            ->add('delete_classeur_after');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\MainBundle\Entity\Collectivite',
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sesile_mainbundle_collectivite';
    }
}
