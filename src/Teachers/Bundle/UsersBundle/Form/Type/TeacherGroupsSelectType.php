<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherGroupsSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'teachers_groups',
                'configs' => [
                    'placeholder' => 'teachers.users.form.choose_teacher_group',
                    'result_template_twig' => 'TeachersUsers:Autocomplete:TeacherGroup/result.html.twig',
                    'selection_template_twig' => 'TeachersUsers:Autocomplete:TeacherGroup/selection.html.twig'
                ],
                'create_form_route' => 'teachers_group_create',
                'grid_name' => 'teachers-groups-grid',
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
        return 'teacher_group_select';
    }
}
