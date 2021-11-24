<?php

namespace Teachers\Bundle\AssignmentBundle\Form\Type;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Teachers\Bundle\AssignmentBundle\Entity\AsmgMsgMailboxProcessSettings;

class AssignmentMessageMailboxProcessType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'teachers_assignment_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Teachers\Bundle\AssignmentBundle\Entity\AsmgMsgMailboxProcessSettings',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            OrganizationUserAclSelectType::class,
            [
                'required' => true,
                'label' => 'oro.case.caseentity.owner.label',
                'constraints' => [
                    new NotNull(),
                ],
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($event->getData()) {
                return;
            }

            $mailbox = $event->getForm()->getRoot()->getData();
            if (!$mailbox instanceof Mailbox) {
                return;
            }

            $processSettings = new AsmgMsgMailboxProcessSettings();
            $mailbox->setProcessSettings($processSettings);
            $event->setData($processSettings);
        });
    }
}
