<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TeacherType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('teacherGroups', TeacherGroupsMultiSelectType::class, [
            'label' => 'teachers.users.teacherGroups.label',
            'required' => true,
            'mapped' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return UserType::class;
    }
}
