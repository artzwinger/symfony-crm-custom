<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceSelectType extends AbstractType
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
                'autocomplete_alias' => 'invoices',
                'configs' => [
                    'entity_name' => 'Teachers\Bundle\InvoiceBundle\Entity\Invoice',
                    'placeholder' => 'teachers.invoice.form.choose_invoice',
                    'result_template_twig' => 'TeachersInvoiceBundle:Autocomplete:Invoices/result.html.twig',
                    'selection_template_twig' => 'TeachersInvoiceBundle:Autocomplete:Invoice/selection.html.twig'
                ],
                'create_form_route' => 'teachers_invoice_create',
                'grid_name' => 'teachers-invoices-grid',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
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
        return 'teacher_invoice_select';
    }
}
