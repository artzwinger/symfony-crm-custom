<?php

namespace Teachers\Bundle\InvoiceBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DOMDocument;
use Exception;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;
use Teachers\Bundle\InvoiceBundle\Helper\Invoice as InvoiceHelper;

class InvoiceController extends AbstractController
{
    /**
     * @param Invoice $invoice
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_invoice_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_invoice_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersInvoiceBundle:Invoice"
     * )
     */
    public function viewAction(Invoice $invoice): array
    {
        return [
            'entity' => $invoice,
            'invoice_has_payments' => $invoice->hasPayments(),
            'can_invoice_receive_payments' => $invoice->canReceivePayments()
        ];
    }

    /**
     * @Route(name="teachers_invoice_index")
     * @Template("@TeachersInvoice/Invoice/index.html.twig")
     * @AclAncestor("teachers_invoice_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Invoice::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_invoice_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Invoice/widget/info.html.twig")
     * @AclAncestor("teachers_invoice_view")
     * @param Invoice $invoice
     * @return array
     */
    public function infoAction(Invoice $invoice): array
    {
        return [
            'entity' => $invoice
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_invoice_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Invoice/update.html.twig")
     * @Acl(
     *      id="teachers_invoice_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersInvoiceBundle:Invoice"
     * )
     * @param Invoice $invoice
     * @return array|RedirectResponse
     */
    public function updateAction(Invoice $invoice)
    {
        return $this->update($invoice, 'teachers_invoice_update');
    }

    /**
     * @Route("/create", name="teachers_invoice_create", options={"expose"=true})
     * @Template("@TeachersInvoice/Invoice/update.html.twig")
     * @Acl(
     *      id="teachers_invoice_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersInvoiceBundle:Invoice"
     * )
     */
    public function createAction()
    {
        $invoice = new Invoice();
        $invoice->setAmountPaid(0);
        $invoice->setAmountRemaining(0);
        $request = $this->get('request_stack')->getCurrentRequest();
        $entityClass = $request->get('entityClass');
        $entityId = $request->get('entityId');
        try {
            if ($entityClass && $entityId) {
                $entityClass = str_replace('_', '\\', $entityClass);
                $repository = $this->get('doctrine.orm.entity_manager')->getRepository($entityClass);
                /** @var Assignment $assignment */
                $assignment = $repository->find($entityId);
                if (empty($assignment)) {
                    throw new EntityNotFoundException();
                }
                $invoice->setAssignment($assignment);
                if ($assignment->getStudent()) {
                    $invoice->setStudent($assignment->getStudent());
                }
                if ($studentContact = $assignment->getStudentContact()) {
                    $invoice->setStudentContact($studentContact);
                }
                if ($studentAccount = $assignment->getStudentAccount()) {
                    $invoice->setStudentAccount($studentAccount);
                }
            }
        } catch (Exception $e) {
        }
        return $this->update($invoice, 'teachers_invoice_create');
    }

    /**
     * @Route("/pay/{id}", name="teachers_invoice_pay", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Invoice/pay.html.twig")
     * @AclAncestor("teachers_invoice_edit")
     * @param Invoice $invoice
     * @return array
     */
    public function payAction(Invoice $invoice)
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createBuilder('Teachers\Bundle\InvoiceBundle\Form\Type\InvoicePayType');
        $form = $builder->getForm();
        return [
            'entity' => $invoice,
            'form' => $form->createView(),
            'formAction' => $this->generateUrl('teachers_invoice_pay_step2', [
                'id' => $invoice->getId()
            ])
        ];
    }

    /**
     * @Route("/pay_step2/{id}", name="teachers_invoice_pay_step2", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersInvoice/Invoice/pay_step2.html.twig")
     * @AclAncestor("teachers_invoice_edit")
     * @param Invoice $invoice
     * @return array
     * @throws Exception
     */
    public function payStep2Action(Invoice $invoice)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $amount = $request->get('amountToPay', 0);
        if ($amount <= 0) {
            $amount = $invoice->getAmountRemaining();
        }
        $redirectUrl = $this->generateUrl('teachers_invoice_pay_step3', [
                'id' => $invoice->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $data = $this->get('teachers_invoice.helper.payment_gateway')->sale($amount, $redirectUrl);
        // Parse Step One's XML response
        $gwResponse = @new SimpleXMLElement($data);
        if ((string)$gwResponse->result == 1) {
            // The form url for used in Step Two below
            $formURL = (string)$gwResponse->{'form-url'};
        } else {
            throw new Exception(print " Error, received " . $data);
        }
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createBuilder('Teachers\Bundle\InvoiceBundle\Form\Type\InvoicePayStep2Type');
        $form = $builder->getForm();
        return [
            'entity' => $invoice,
            'form' => $form->createView(),
            'formAction' => $formURL
        ];
    }

    /**
     * @Route("/pay_step3/{id}", name="teachers_invoice_pay_step3", requirements={"id"="\d+"}, options={"expose"=true})
     * @AclAncestor("teachers_invoice_edit")
     * @param Invoice $invoice
     * @return RedirectResponse
     * @throws Exception
     */
    public function payStep3Action(Invoice $invoice)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $tokenId = $request->get('token-id');
        $data = $this->get('teachers_invoice.helper.payment_gateway')->completeAction($tokenId);
        $gwResponse = @new SimpleXMLElement((string)$data);
        $nmiSuccess = (string)$gwResponse->result == 1;

        if ($nmiSuccess) {
            $payment = new Payment();
            $payment->setInvoice($invoice);
            $payment->setAmountPaid((string)$gwResponse->amount);
            $payment->setAmountPaidAfterRefund($payment->getAmountPaid());
            if ($invoice->getStudent()) {
                $payment->setOwner($invoice->getStudent());
            }
            $payment->setOrganization($invoice->getOrganization());
            $payment->setTransaction((string)$gwResponse->{'transaction-id'});
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($payment);
            $em->flush($payment);
        }

        $this->get('session')->getFlashBag()->add(
            $nmiSuccess ? 'success' : 'error',
            (string)$gwResponse->{'result-text'}
        );

        return $this->redirectToRoute('teachers_invoice_view', [
            'id' => $invoice->getId()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="teachers_invoice_delete", requirements={"id"="\d+"}, methods={"DELETE"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_invoice_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersInvoiceBundle:Invoice"
     * )
     * @CsrfProtection()
     * @param Invoice $invoice
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Invoice $invoice): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($invoice);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @Route("/sendEmail/{id}", name="teachers_invoice_send_email", requirements={"id"="\d+"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_invoice_send_email",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersInvoiceBundle:Invoice"
     * )
     * @CsrfProtection()
     * @param Invoice $invoice
     * @return JsonResponse
     */
    public function sendEmailAction(Invoice $invoice): JsonResponse
    {
        /** @var InvoiceHelper $helper */
        $helper = $this->get('teachers_invoice.helper.invoice');
        $helper->sendEmailForInvoice($invoice);
        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Invoice $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(Invoice $entity, $action)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.invoice.controller.invoice.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder('teachers_invoice_form', 'Teachers\Bundle\InvoiceBundle\Form\Type\InvoiceType');
        $builder->setAction($action);

        return $builder->getForm();
    }
}
