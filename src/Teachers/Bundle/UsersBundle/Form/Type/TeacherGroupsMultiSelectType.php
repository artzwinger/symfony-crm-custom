<?php

namespace Teachers\Bundle\UsersBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherGroupsMultiSelectType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'teachers_groups',
                'configs' => [
                    'entity_name' => 'Teachers\Bundle\UsersBundle\Entity\TeacherGroup',
                    'multiple' => true,
                    'placeholder' => 'teachers.users.form.choose_teacher_groups',
                    'result_template_twig' => 'TeachersUsersBundle:Autocomplete:TeacherGroup/result.html.twig',
                    'selection_template_twig' => 'TeachersUsersBundle:Autocomplete:TeacherGroup/selection.html.twig'
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
        return OroJquerySelect2HiddenType::class;
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
        return 'teacher_group_multi_select';
    }
}
