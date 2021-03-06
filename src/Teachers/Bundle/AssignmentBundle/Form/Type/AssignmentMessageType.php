<?php

namespace Teachers\Bundle\AssignmentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'message',
            TextareaType::class,
            [
                'label' => 'teachers.assignment.message.message.label'
            ]
        )->add(
            'denialReason',
            TextareaType::class,
            [
                'label' => 'teachers.assignment.message.denialReason.label',
                'required' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Teachers\\Bundle\\AssignmentBundle\\Entity\\AssignmentMessage',
                'csrf_token_id' => 'teachers_assignment_message',
                'ownership_disabled' => true,
            ]
        );
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
        return 'teachers_assignment_message';
    }
}
