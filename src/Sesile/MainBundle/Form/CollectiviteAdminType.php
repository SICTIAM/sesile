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


class CollectiviteAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deleteClasseurAfter', NumberType::class, array(
                "label" => 'label.daysClasseur'
            ))
            ->add('submit', SubmitType::class, array('label' => 'button.submit'))
            ->remove('pageSignature')
            ->remove('titreVisa')
            ->remove('textmailnew')
            ->remove('textmailrefuse')
            ->remove('textmailwalid')
            ->remove('message')
            ->remove('nom')
            ->remove('active')
            ->remove('file')
            ->remove('abscissesVisa')
            ->remove('ordonneesVisa')
            ->remove('abscissesSignature')
            ->remove('ordonneesSignature')
            ->remove('couleurVisa')
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
    /*public function getBlockPrefix()
    {
        return 'sesile_mainbundle_collectivite';
    }*/
    public function getParent()
    {
        return CollectiviteType::class;
    }
}
