<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use DateTime;
use DateTimeZone;
use Exception;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

/**
 * Form type for Invoice entity
 */
class InvoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amountOwed', OroMoneyType::class, [
                'required' => true,
                'label' => 'teachers.invoice.amountOwed.label'
            ])
            ->add('dueDate', OroDateTimeType::class, [
                'required' => true,
                'label' => 'teachers.invoice.due_date.label',
                'constraints' => [
                    $this->getDueDateValidationConstraint(new DateTime('now', new DateTimeZone('UTC')))
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_invoice';
    }

    /**
     * @param DateTime $startDate
     *
     * @return Assert\GreaterThanOrEqual
     */
    protected function getDueDateValidationConstraint(DateTime $startDate): Assert\GreaterThanOrEqual
    {
        return new Assert\GreaterThanOrEqual(
            [
                'value' => $startDate,
                'message' => 'teachers.invoice.due_date_not_in_the_past'
            ]
        );
    }
}
