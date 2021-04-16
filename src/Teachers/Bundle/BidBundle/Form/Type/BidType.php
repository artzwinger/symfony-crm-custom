<?php

namespace Teachers\Bundle\BidBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\BidBundle\Entity\Bid;
use Teachers\Bundle\UsersBundle\Form\Type\TeacherSelectType;

/**
 * Form type for Bid entity
 */
class BidType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => true,
                'label' => 'teachers.bid.subject.label'
            ])
            ->add('price', OroMoneyType::class, [
                'required' => false,
                'label' => 'teachers.bid.price.label'
            ])
            ->add('teacher', TeacherSelectType::class, [
                'label' => 'teachers.bid.teacher.label',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Bid::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_bid';
    }
}
