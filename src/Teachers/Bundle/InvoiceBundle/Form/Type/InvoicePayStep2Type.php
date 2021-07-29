<?php

namespace Teachers\Bundle\InvoiceBundle\Form\Type;

use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Form type for Invoice entity
 */
class InvoicePayStep2Type extends AbstractType
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billing-cc-number', TextType::class, [
                'required' => true,
                'label' => 'teachers.invoice.cardNumber.label'
            ])
            ->add('billing-cc-exp', TextType::class, [
                'required' => true,
                'label' => 'teachers.invoice.cardExp.label',
                'constraints' => [
                    new Regex([
                        'pattern' => '#^([0-9]{2})\/([0-9]{2})$#',
                        'message' => "The expiration date must be mm/yy",
                    ])
                ]
            ])
            ->add('billing-cvv', PasswordType::class, [
                'required' => true,
                'label' => 'teachers.invoice.cardSecret.label'
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
