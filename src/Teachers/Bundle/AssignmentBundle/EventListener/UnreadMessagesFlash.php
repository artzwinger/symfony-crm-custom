<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\UsersBundle\Helper\Role;

class UnreadMessagesFlash
{
    const SESSION_KEY = 'unread_messages_flash';
    /**
     * @var Role
     */
    private $roleHelper;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param Role $roleHelper
     * @param SessionInterface $session
     * @param FlashBagInterface $flashBag
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManager       $entityManager,
        Role                $roleHelper,
        SessionInterface    $session,
        FlashBagInterface   $flashBag,
        TranslatorInterface $translator
    )
    {
        $this->entityManager = $entityManager;
        $this->roleHelper = $roleHelper;
        $this->session = $session;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function onKernelView(ViewEvent $event)
    {
        $shownData = $this->session->get(self::SESSION_KEY);
        if ($shownData && time() - $shownData['timestamp'] < 300) {
            return;
        }
        if ($this->showFlash()) {
            $this->session->set(self::SESSION_KEY, [
                'value' => true,
                'timestamp' => time()
            ]);
        }
    }

    public function showFlash(): bool
    {
        $user = $this->roleHelper->getCurrentUser();
        if (!$user instanceof User) {
            return false;
        }
        $repo = $this->entityManager->getRepository(AssignmentMessage::class);
        $msg = $repo->findOneBy([
            'recipient' => $user,
            'viewedByRecipient' => false
        ]);
        if (!$msg) {
            return false;
        }
        $this->flashBag->add('warning', $this->translator->trans('teachers.assignment.message.has_unread_message_notice'));
        return true;
    }
}
