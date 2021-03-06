<?php

namespace Teachers\Bundle\ApplicationBundle\Form\Type;

use DateTime;
use DateTimeZone;
use Exception;
use Oro\Bundle\ApiBundle\Form\Type\BooleanType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.first_name.label'
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.last_name.label'
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.email.label'
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.phone.label'
            ])
            ->add('courseName', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.course_name.label'
            ])
            ->add('coursePrefixes', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.course_prefixes.label'
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'teachers.application.description.label'
            ])
            ->add('amountDueToday', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.application.amountDueToday.label',
                'constraints' => [
                    $this->getAmountDueTodayConstraint()
                ]
            ])
            ->add('price', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.application.price.label',
                'constraints' => [new Assert\GreaterThanOrEqual(0)]
            ])
            ->add('workToday', ChoiceType::class, [
                'choices' => [
                    'teachers.application.work_today.true.label' => true,
                    'teachers.application.work_today.false.label' => false,
                ],
                'required' => true,
                'label' => 'teachers.application.work_today.label',
            ])
            ->add('dueDate', OroDateTimeType::class, [
                'required' => true,
                'label' => 'teachers.application.due_date.label',
                'constraints' => [
                    $this->getDueDateValidationConstraint(new DateTime('now', new DateTimeZone('UTC')))
                ]
            ])
            ->add('classStartDate', OroDateType::class, [
                'required' => false,
                'label' => 'teachers.application.class_start_date.label',
                'constraints' => [
                    $this->getDueDateValidationConstraint(new DateTime('-1 day', new DateTimeZone('UTC')))
                ]
            ])
            ->add('courseUrl', TextType::class, [
                'required' => true,
                'label' => 'teachers.application.course_url.label'
            ])
            ->add('userLogin', TextType::class, [
                'required' => false,
                'label' => 'teachers.application.user_login.label'
            ])
            ->add('userPassword', TextType::class, [
                'required' => false,
                'label' => 'teachers.application.user_password.label'
            ])
            ->add('instructions', TextareaType::class, [
                'required' => true,
                'label' => 'teachers.application.instructions.label'
            ])
            ->add('status', EnumSelectType::class, [
                'label' => 'teachers.application.status.label',
                'enum_code' => 'application_status',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ])
            ->add('rep', EnumSelectType::class, [
                'label' => 'teachers.application.rep.label',
                'enum_code' => 'application_rep',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ])
            ->add('term', EnumSelectType::class, [
                'label' => 'teachers.application.term.label',
                'enum_code' => 'application_term',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
            ]);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $this->updateAmountDueTodayConstraints($event);
    }

    /**
     * @param FormEvent $event
     */
    protected function updateAmountDueTodayConstraints(FormEvent $event)
    {
        $data = $event->getData();
        if ($data && $data['price'] > 0) {
            FormUtils::replaceField(
                $event->getForm(),
                'amountDueToday',
                [
                    'constraints' => [
                        $this->getAmountDueTodayConstraint(floatval($data['price']))
                    ]
                ]
            );
        }
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

    protected function getAmountDueTodayConstraint($totalPrice = PHP_INT_MAX): Assert\LessThanOrEqual
    {
        return new Assert\LessThanOrEqual(
            [
                'value' => $totalPrice,
                'message' => 'teachers.application.amount_due_today_not_less_than_total_price'
            ]
        );
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
                'message' => 'teachers.application.due_date_not_in_the_past'
            ]
        );
    }
}
