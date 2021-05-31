<?php

namespace Teachers\Bundle\InvoiceBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\EmailBundle\Tools\AggregatedEmailTemplatesSender;
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
use Teachers\Bundle\InvoiceBundle\Helper\Invoice as InvoiceHelper;
use Twig\Error\Error;

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
            'entity' => $invoice
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
