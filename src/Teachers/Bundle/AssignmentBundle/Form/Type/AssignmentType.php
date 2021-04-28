<?php

namespace Teachers\Bundle\AssignmentBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\UsersBundle\Form\Type\StudentSelectType;
use Teachers\Bundle\UsersBundle\Form\Type\TeacherGroupsMultiSelectType;

/**
 * Form type for Assignment entity
 */
class AssignmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.subject.label'
            ])
            ->add('description', OroResizeableRichTextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.description.label'
            ])
            ->add('status', EnumSelectType::class, [
                'label' => 'teachers.assignment.status.label',
                'enum_code' => 'assignment_status',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ])
            ->add('teacherGroups', TeacherGroupsMultiSelectType::class, [
                'label' => 'teachers.assignment.teacherGroups.label',
                'required' => true
            ])
//            ->add('courseManager', CourseManagerSelectType::class, [
//                'label' => 'teachers.assignment.course_manager.label',
//                'required' => true
//            ])
//            ->add('teacher', TeacherSelectType::class, [
//                'label' => 'teachers.assignment.teacher.label',
//                'required' => false
//            ])
            ->add('student', StudentSelectType::class, [
                'label' => 'teachers.assignment.student.label',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Assignment::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_assignment';
    }
}
