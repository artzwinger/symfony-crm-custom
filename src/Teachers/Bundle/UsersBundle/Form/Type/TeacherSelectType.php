<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\UserSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'configs' => [
                    'placeholder' => 'teachers.users.form.choose_teacher',
                ],
                'create_form_route' => 'teachers_teacher_create',
                'grid_name' => 'teacher-select-grid',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return UserSelectType::class;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'course_manager_select';
    }
}
