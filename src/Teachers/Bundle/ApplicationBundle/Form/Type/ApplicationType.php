<?php

namespace Teachers\Bundle\ApplicationBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

/**
 * Form type for Application entity
 */
class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.subject.label'
            ])
            ->add('description', OroResizeableRichTextType::class, [
                'required' => false,
                'label' => 'teachers.application.description.label'
            ])
            ->add('studentLoginInfo', OroResizeableRichTextType::class, [
                'required' => false,
                'label' => 'teachers.application.studentLoginInfo.label'
            ])
            ->add('price', OroMoneyType::class, [
                'required' => false,
                'label' => 'teachers.application.price.label'
            ])
            ->add('status', EnumSelectType::class, [
                'label' => 'teachers.application.status.label',
                'enum_code' => 'application_status',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_application';
    }
}
