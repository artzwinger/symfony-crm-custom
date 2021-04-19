<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\UsersBundle\Helper\Role;

class TeacherSelectType extends AbstractType
{
    /**
     * @var \Teachers\Bundle\UsersBundle\Helper\Role
     */
    private $roleHelper;

    public function __construct(Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'users_only_teachers',
                'configs' => [
                    'placeholder' => 'teachers.users.form.choose_teacher',
                    'result_template_twig' => 'OroUserBundle:User:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroUserBundle:User:Autocomplete/selection.html.twig'
                ],
                'create_form_route' => 'teachers_teacher_create',
                'grid_name' => 'users-by-role-select-grid',
                'grid_parameters' => [
                    'role_id' => $this->roleHelper->getStudentRoleId()
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
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
        return 'teacher_select';
    }
}
