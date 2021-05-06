<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\UsersBundle\Helper\Role;

class TeacherMultiSelectType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var Role
     */
    private $roleHelper;

    /**
     * @param EntityManager $entityManager
     * @param Role $roleHelper
     */
    public function __construct(EntityManager $entityManager, Role $roleHelper)
    {
        $this->entityManager = $entityManager;
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
                    'entity_name' => 'Oro\Bundle\UserBundle\Entity\User',
                    'multiple' => true,
                    'placeholder' => 'teachers.users.form.choose_teachers',
                    'result_template_twig' => 'OroUserBundle:User:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroUserBundle:User:Autocomplete/selection.html.twig'
                ],
                'create_form_route' => 'teachers_teacher_create',
                'grid_name' => 'users-by-role-select-grid',
                'grid_parameters' => [
                    'role_id' => $this->roleHelper->getTeacherRoleId()
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new EntitiesToIdsTransformer($this->entityManager, $options['entity_class'])
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
        return 'teacher_multi_select';
    }
}
