<?php

namespace Teachers\Bundle\SatisfactionBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\ReminderBundle\Form\Type\ReminderCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\SatisfactionBundle\Entity\Satisfaction;

/**
 * Form type for Task entity
 */
class SatisfactionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'description',
                OroResizeableRichTextType::class,
                [
                    'required' => false,
                    'label' => 'teachers.satisfaction.description.label'
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
                'data_class' => Satisfaction::class
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'teachers_satisfaction_satisfaction';
    }
}
