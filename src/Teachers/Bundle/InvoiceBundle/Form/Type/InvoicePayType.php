<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Exception;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for Invoice entity
 */
class InvoicePayType extends AbstractType
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
