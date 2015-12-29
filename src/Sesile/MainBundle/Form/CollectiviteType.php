<?php

namespace Sesile\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;


class CollectiviteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array("label" => "Nom"))
            ->add('active', 'checkbox', array("label" => "Active", 'required' => false))
            ->add('file', 'file', array('label' => 'Logo de la collectivité',
                'data_class' => null,
                'required' => false
            ))
            ->add('textmailnew', 'textarea', array('label' => "Texte du mail d'un nouveau classeur", 'required' => false))
            ->add('textmailrefuse', 'textarea', array('label' => "Texte du mail d'un classeur refusé", 'required' => false))
            ->add('textmailwalid', 'textarea', array('label' => "Texte du mail d'un classeur validé", 'required' => false, 'attr' => array('name' => 'textmailwalid')))
            ->add('message', 'textarea', array('label' => "Message d'accueil", 'required' => false, 'attr' => array('name' => 'message')))
/*            ->add('abscissesVisa', 'text', array("label" => "Abscisses"))
            ->add('ordonneesVisa', 'text', array("label" => "Ordonnees"))
            ->add('abscissesSignature', 'text', array("label" => "Abscisses"))
            ->add('ordonneesSignature', 'text', array("label" => "Ordonnees"))*/
            ->add('couleurVisa', 'hidden', array("label" => "Couleur"))
            ->add('titreVisa', 'text', array("label" => "Titre"))
            ->add('pageSignature', 'choice', array("label" => "Page",'choices'=>array(0=>'Première page',1=>'Dernière page')));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\MainBundle\Entity\Collectivite'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sesile_mainbundle_collectivite';
    }
}
