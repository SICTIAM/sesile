<?php

namespace Sesile\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('nom', TextType::class, array("label" => "Nom"))
            ->add('active', CheckboxType::class, array("label" => "Active", 'required' => false))
            ->add('file', FileType::class, array('label' => 'Logo de la collectivité',
                'data_class' => null,
                'required' => false
            ))
            ->add('textmailnew', TextareaType::class, array('label' => "Texte du mail d'un nouveau classeur", 'required' => false))
            ->add('textmailrefuse', TextareaType::class, array('label' => "Texte du mail d'un classeur refusé", 'required' => false))
            ->add('textmailwalid', TextareaType::class, array('label' => "Texte du mail d'un classeur validé", 'required' => false, 'attr' => array('name' => 'textmailwalid')))
            ->add('message', TextareaType::class, array('label' => "Message d'accueil", 'required' => false, 'attr' => array('name' => 'message')))
            ->add('abscissesVisa', HiddenType::class, array("label" => "Abscisses"))
            ->add('ordonneesVisa', HiddenType::class, array("label" => "Ordonnees"))
            ->add('abscissesSignature', HiddenType::class, array("label" => "Abscisses"))
            ->add('ordonneesSignature', HiddenType::class, array("label" => "Ordonnees"))
            ->add('couleurVisa', HiddenType::class, array("label" => "Couleur"))
            ->add('titreVisa', TextType::class, array("label" => "Titre"))
            ->add('pageSignature', ChoiceType::class, array(
                'choices' => array(
                    'Dernière page' =>  0,
                    'Première page' =>  1
                ),
                "label" => "Page",
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\MainBundle\Entity\Collectivite'
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
