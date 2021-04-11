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
use Teachers\Bundle\UsersBundle\Form\Type\CourseManagerSelectType;
use Teachers\Bundle\UsersBundle\Form\Type\StudentSelectType;
use Teachers\Bundle\UsersBundle\Form\Type\TeacherSelectType;

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
                'label' => 'teachers.assignment.assignment.subject.label'
            ])
            ->add('description', OroResizeableRichTextType::class, [
                'required' => false,
                'label' => 'teachers.assignment.assignment.description.label'
            ])
            ->add('status', EnumSelectType::class, [
                'label' => 'teachers.assignment.assignment.status.label',
                'enum_code' => 'assignment_status',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ])
//            ->add('courseManager', CourseManagerSelectType::class, [
//                'label' => 'teachers.assignment.assignment.course_manager.label',
//                'required' => true
//            ])
            ->add('teacher', TeacherSelectType::class, [
                'label' => 'teachers.assignment.assignment.teacher.label',
                'required' => true
            ])
            ->add('student', StudentSelectType::class, [
                'label' => 'teachers.assignment.assignment.student.label',
                'required' => true
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
