<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

/**
 * Form type for Invoice entity
 */
class PaymentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amountPaid', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.invoice.amountPaid.label',
                'constraints' => [
                    $this->getAmountPaidValidationConstraint($options['max_payment_value'])
                ]
            ]);
    }

    /**
     * @param float $max
     * @return Assert\LessThanOrEqual
     */
    protected function getAmountPaidValidationConstraint(float $max): Assert\LessThanOrEqual
    {
        return new Assert\LessThanOrEqual(
            [
                'value' => $max,
                'message' => 'teachers.invoice.max_payment_value'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'max_payment_value' => PHP_FLOAT_MAX
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_payment';
    }
}
