<?php

namespace Teachers\Bundle\AssignmentBundle\Form\Type;

use DateTime;
use DateTimeZone;
use Exception;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\UsersBundle\Form\Type\CourseManagerSelectType;
use Teachers\Bundle\UsersBundle\Form\Type\StudentSelectType;
use Teachers\Bundle\UsersBundle\Form\Type\TeacherGroupsMultiSelectType;

/**
 * Form type for Assignment entity
 */
class AssignmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.first_name.label'
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.last_name.label'
            ])
            ->add('courseName', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.course_name.label'
            ])
            ->add('coursePrefixes', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.course_prefixes.label'
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'teachers.assignment.description.label'
            ])
            ->add('workToday', ChoiceType::class, [
                'choices' => [
                    'teachers.assignment.work_today.true.label' => true,
                    'teachers.assignment.work_today.false.label' => false,
                ],
                'required' => true,
                'label' => 'teachers.assignment.work_today.label',
            ])
            ->add('dueDate', OroDateTimeType::class, [
                'required' => true,
                'label' => 'teachers.assignment.due_date.label',
                'constraints' => [
                    $this->getDueDateValidationConstraint(new DateTime('now', new DateTimeZone('UTC')))
                ]
            ])
            ->add('courseUrl', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.course_url.label'
            ])
            ->add('userLogin', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.user_login.label'
            ])
            ->add('userPassword', TextType::class, [
                'required' => true,
                'label' => 'teachers.assignment.user_password.label'
            ])
            ->add('instructions', TextareaType::class, [
                'required' => true,
                'label' => 'teachers.assignment.instructions.label'
            ])
            ->add('amountDueToday', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.assignment.amountDueToday.label',
                'constraints' => [new Assert\GreaterThanOrEqual(0)]
            ])
            ->add('term', EnumSelectType::class, [
                'label' => 'teachers.assignment.term.label',
                'enum_code' => 'application_term',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
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

    /**
     * @param FormEvent $event
     */
    protected function updateDueDateFieldConstraints(FormEvent $event)
    {
        /** @var Assignment $data */
        $data = $event->getData();
        if ($data && $data->getCreatedAt()) {
            FormUtils::replaceField(
                $event->getForm(),
                'dueDate',
                [
                    'constraints' => [
                        $this->getDueDateValidationConstraint($data->getCreatedAt())
                    ]
                ]
            );
        }
    }

    /**
     * @param DateTime $startDate
     *
     * @return Assert\GreaterThanOrEqual
     */
    protected function getDueDateValidationConstraint(DateTime $startDate): Assert\GreaterThanOrEqual
    {
        return new Assert\GreaterThanOrEqual(
            [
                'value' => $startDate,
                'message' => 'teachers.assignment.due_date_not_in_the_past'
            ]
        );
    }
}
