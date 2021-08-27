<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Exception;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class InvoiceManualPayType extends AbstractType
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amountToPay', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.invoice.amountToPay.label'
            ])
            ->add('reason', TextType::class, [
                'required' => true,
                'label' => 'teachers.invoice.reason.label',
                'constraints' => [
                    new Length(['max' => 255]),
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
