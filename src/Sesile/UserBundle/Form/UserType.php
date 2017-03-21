<?php

namespace Sesile\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;


class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', EmailType::class, array('label' => 'Email', 'label_attr' => array('class' => 'sesile_userbundle_user_username_label')))
            ->add('email', HiddenType::class)
            ->add('Nom', TextType::class)
            ->add('Prenom', TextType::class, array('label' => 'Prénom'))
            ->add('ville', TextType::class, array('required' => false,))
            ->add('cp', TextType::class, array('label' => 'Code Postal', 'required' => false,))
            ->add('departement', TextType::class, array('label' => 'Département', 'required' => false,))
            ->add('pays', TextType::class, array('required' => false,))
            ->add('userRole', CollectionType::class, array(
                'label' => '',
                'entry_type' => UserRoleType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'entry_options' => array('attr' => array('class' => ''))))
            ->add('qualite', TextareaType::class, array('label' => 'Qualité', 'required' => false, 'attr' => array('cols' => '37', 'class' => 'qualite', 'max_length' => 250), 'label_attr' => array('class' => 'label_form_textarea')))
            ->add('enabled', CheckboxType::class, array('label' => 'Activé', 'required' => false))
            ->add('apiactivated', CheckboxType::class, array('label' => 'API', 'required' => false))
            ->add('apitoken', TextType::class, array('attr' => array('readonly')))
            ->add('apisecret', TextType::class, array('attr' => array('readonly')))
            ->add('file', FileType::class, array('label' => 'Avatar',
                'data_class' => null,
                'required' => false,
            ))
            ->add('fileSignature', FileType::class, array('label' => 'Signature',
                'data_class' => null,
                'required' => false,
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sesile\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sesile_userbundle_user';
    }
}
