<?php

namespace Teachers\Bundle\InvoiceBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;
use Teachers\Bundle\InvoiceBundle\Entity\Refund;

class RefundController extends AbstractController
{
    /**
     * @param Refund $refund
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_refund_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_refund_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersInvoiceBundle:Refund"
     * )
     */
    public function viewAction(Refund $refund): array
    {
        return [
            'entity' => $refund
        ];
    }

    /**
     * @Route(name="teachers_refund_index")
     * @Template("@TeachersInvoice/Refund/index.html.twig")
     * @AclAncestor("teachers_refund_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Refund::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_refund_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Refund/widget/info.html.twig")
     * @AclAncestor("teachers_refund_view")
     * @param Refund $refund
     * @return array
     */
    public function infoAction(Refund $refund): array
    {
        return [
            'entity' => $refund
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_refund_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Refund/update.html.twig")
     * @Acl(
     *      id="teachers_refund_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersInvoiceBundle:Refund"
     * )
     * @param Refund $refund
     * @return array|RedirectResponse
     */
    public function updateAction(Refund $refund)
    {
        return $this->update($refund, 'teachers_refund_update', PHP_FLOAT_MAX);
    }

    /**
     * @Route("/create", name="teachers_refund_create", options={"expose"=true})
     * @Template("@TeachersInvoice/Refund/update.html.twig")
     * @Acl(
     *      id="teachers_refund_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersInvoiceBundle:Refund"
     * )
     */
    public function createAction()
    {
        $refund = new Refund();
        $request = $this->get('request_stack')->getCurrentRequest();
        $entityId = $request->get('entityId');
        try {
            if ($entityId) {
                $repository = $this->get('doctrine.orm.entity_manager')->getRepository(Payment::class);
                /** @var Payment $payment */
                $payment = $repository->find($entityId);
                if (empty($payment)) {
                    throw new EntityNotFoundException();
                }
                $invoice = $payment->getInvoice();
                $refund->setPayment($payment);
                $refund->setInvoice($invoice);
                if ($invoice->getStudent()) {
                    $refund->setOwner($invoice->getStudent());
                }
            }
        } catch (Exception $e) {
        }
        return $this->update($refund, 'teachers_refund_create');
    }

    /**
     * @Route("/delete/{id}", name="teachers_refund_delete", requirements={"id"="\d+"}, methods={"DELETE"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_refund_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersInvoiceBundle:Refund"
     * )
     * @CsrfProtection()
     * @param Refund $refund
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Refund $refund): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($refund);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Refund $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(Refund $entity, $action)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action, $entity);

        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.invoice.controller.invoice.saved.message')
        );
    }

    private function getForm($action, Refund $refund): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $remaining = PHP_FLOAT_MAX;
        if ($payment = $refund->getPayment()) {
            $remaining = $payment->getAmountPaid();
            foreach ($payment->getRefunds() as $refund) {
                $remaining -= $refund->getAmountRefunded();
            }
        }
        $builder = $factory->createNamedBuilder('teachers_refund_form', 'Teachers\Bundle\InvoiceBundle\Form\Type\RefundType', $refund, [
            'max_value' => $remaining
        ]);
        $builder->setAction($action);

        return $builder->getForm();
    }
}
