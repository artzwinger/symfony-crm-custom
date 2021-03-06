<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroup;

/**
 * Form type for TeacherGroup entity
 */
class TeacherGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'teachers.users.teacher_group.title.label'
            ])
            ->add('description', OroResizeableRichTextType::class, [
                'required' => false,
                'label' => 'teachers.users.teacher_group.description.label'
            ])
            ->add('teachers', TeacherMultiSelectType::class, [
                'label' => 'teachers.users.teacher_group.teachers.label',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TeacherGroup::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_group';
    }
}
