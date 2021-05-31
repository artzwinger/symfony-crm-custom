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
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class PaymentController extends AbstractController
{
    /**
     * @param Payment $payment
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_payment_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_payment_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersInvoiceBundle:Payment"
     * )
     */
    public function viewAction(Payment $payment): array
    {
        return [
            'entity' => $payment
        ];
    }

    /**
     * @Route(name="teachers_payment_index")
     * @Template("@TeachersInvoice/Payment/index.html.twig")
     * @AclAncestor("teachers_payment_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Payment::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_payment_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Payment/widget/info.html.twig")
     * @AclAncestor("teachers_payment_view")
     * @param Payment $payment
     * @return array
     */
    public function infoAction(Payment $payment): array
    {
        return [
            'entity' => $payment
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_payment_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Payment/update.html.twig")
     * @Acl(
     *      id="teachers_payment_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersInvoiceBundle:Payment"
     * )
     * @param Payment $payment
     * @return array|RedirectResponse
     */
    public function updateAction(Payment $payment)
    {
        return $this->update($payment, 'teachers_payment_update', PHP_FLOAT_MAX);
    }

    /**
     * @Route("/create", name="teachers_payment_create", options={"expose"=true})
     * @Template("@TeachersInvoice/Payment/update.html.twig")
     * @Acl(
     *      id="teachers_payment_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersInvoiceBundle:Payment"
     * )
     */
    public function createAction()
    {
        $payment = new Payment();
        $request = $this->get('request_stack')->getCurrentRequest();
        $entityId = $request->get('entityId');
        $remaining = PHP_FLOAT_MAX;
        try {
            if ($entityId) {
                $repository = $this->get('doctrine.orm.entity_manager')->getRepository(Invoice::class);
                /** @var Invoice $invoice */
                $invoice = $repository->find($entityId);
                if (empty($invoice)) {
                    throw new EntityNotFoundException();
                }
                $remaining = $invoice->getAmountRemaining();
                $payment->setInvoice($invoice);
                if ($invoice->getStudent()) {
                    $payment->setOwner($invoice->getStudent());
                }
            }
        } catch (Exception $e) {
        }
        return $this->update($payment, 'teachers_payment_create', $remaining);
    }

    /**
     * @Route("/delete/{id}", name="teachers_payment_delete", requirements={"id"="\d+"}, methods={"DELETE"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_payment_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersInvoiceBundle:Payment"
     * )
     * @CsrfProtection()
     * @param Payment $payment
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Payment $payment): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($payment);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Payment $entity
     * @param $action
     * @param $remaining
     * @return RedirectResponse|array
     */
    private function update(Payment $entity, $action, $remaining)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action, $entity, $remaining);

        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.invoice.controller.invoice.saved.message')
        );
    }

    private function getForm($action, Payment $payment, $remaining): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder('teachers_payment_form', 'Teachers\Bundle\InvoiceBundle\Form\Type\PaymentType', $payment, [
            'max_payment_value' => $remaining
        ]);
        $builder->setAction($action);

        return $builder->getForm();
    }
}
