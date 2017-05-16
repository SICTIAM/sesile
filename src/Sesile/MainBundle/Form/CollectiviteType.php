<?php

namespace Sesile\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('nom', TextType::class, array("label" => 'label.name'))
            ->add('active', CheckboxType::class, array("label" => 'label.active', 'required' => false))
            ->add('file', FileType::class, array('label' => 'label.logo',
                'data_class' => null,
                'required' => false
            ))
            ->add('textmailnew', TextareaType::class, array('label' => 'label.newClasseur', 'required' => false))
            ->add('textmailrefuse', TextareaType::class, array('label' => 'label.rejectClasseur', 'required' => false))
            ->add('textmailwalid', TextareaType::class, array('label' => 'label.validClasseur', 'required' => false, 'attr' => array('name' => 'textmailwalid')))
            ->add('message', TextareaType::class, array('label' => 'label.homeNotification', 'required' => false, 'attr' => array('name' => 'message')))
            ->add('abscissesVisa', HiddenType::class, array("label" => 'label.abscissa'))
            ->add('ordonneesVisa', HiddenType::class, array("label" => 'label.ordinates'))
            ->add('abscissesSignature', HiddenType::class, array("label" => 'label.abscissa'))
            ->add('ordonneesSignature', HiddenType::class, array("label" => 'label.ordinates'))
            ->add('couleurVisa', HiddenType::class, array("label" => 'label.color'))
            ->add('titreVisa', TextType::class, array("label" => 'label.title'))
            ->add('pageSignature', ChoiceType::class, array(
                'choices' => array(
                    'label.lastPage' =>  0,
                    'label.firstPage' =>  1
                ),
                "label" => 'label.page',
            ))
            ->add('deleteClasseurAfter', NumberType::class, array(
                "label" => 'label.daysClasseur'
            ))
            ->add('submit', SubmitType::class, array('label' => 'button.submit'))
        ;

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\MainBundle\Entity\Collectivite',
            'translation_domain' => 'Collectivite',
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
