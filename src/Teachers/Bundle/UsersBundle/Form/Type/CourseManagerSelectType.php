<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\UserAclSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseManagerSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'configs' => [
                    'placeholder' => 'teachers.users.form.choose_course_manager',
                ],
                'grid_name' => 'course-manager-select-grid',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return UserAclSelectType::class;
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
