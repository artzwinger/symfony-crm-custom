<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use DateTime;
use DateTimeZone;
use Exception;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add('rep', EnumSelectType::class, [
                'label' => 'teachers.invoice.rep.label',
                'enum_code' => 'invoice_rep',
                'required' => true,
                'constraints' => [new Assert\NotNull()]
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
