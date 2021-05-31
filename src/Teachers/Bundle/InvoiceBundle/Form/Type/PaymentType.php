<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\TaskBundle\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;
use Symfony\Component\Validator\Constraints as Assert;

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
