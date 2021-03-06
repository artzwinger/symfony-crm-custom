<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote;

class AssignmentPrivateNoteController extends AbstractController
{
    /**
     * @param AssignmentPrivateNote $assignmentPrivateNote
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_private_note_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_assignment_private_note_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:AssignmentPrivateNote"
     * )
     */
    public function viewAction(AssignmentPrivateNote $assignmentPrivateNote): array
    {
        return [
            'entity' => $assignmentPrivateNote
        ];
    }

    /**
     * @Route(name="teachers_assignment_private_note_index")
     * @Template("@TeachersAssignment/AssignmentPrivateNote/index.html.twig")
     * @AclAncestor("teachers_assignment_private_note_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => AssignmentPrivateNote::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_private_note_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentPrivateNote/widget/info.html.twig")
     * @AclAncestor("teachers_assignment_private_note_view")
     * @param AssignmentPrivateNote $assignmentPrivateNote
     * @return array
     */
    public function infoAction(AssignmentPrivateNote $assignmentPrivateNote): array
    {
        return [
            'entity' => $assignmentPrivateNote
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_assignment_private_note_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentPrivateNote/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_private_note_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersAssignmentBundle:AssignmentPrivateNote"
     * )
     * @param AssignmentPrivateNote $assignmentPrivateNote
     * @return array|RedirectResponse
     */
    public function updateAction(AssignmentPrivateNote $assignmentPrivateNote)
    {
        return $this->update($assignmentPrivateNote, 'teachers_assignment_private_note_update');
    }

    /**
     * @Route("/create", name="teachers_assignment_private_note_create", options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentPrivateNote/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_private_note_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentPrivateNote"
     * )
     */
    public function createAction()
    {
        return $this->update(new AssignmentPrivateNote(), 'teachers_assignment_private_note_create');
    }

    /**
     * @Route(
     *     "/delete/{id}",
     *     name="teachers_assignment_private_note_delete",
     *     requirements={"id"="\d+"},
     *     methods={"DELETE"},
     *     options={"expose"=true}
     * )
     * @Acl(
     *      id="teachers_assignment_private_note_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersAssignmentBundle:AssignmentPrivateNote"
     * )
     * @CsrfProtection()
     * @param AssignmentPrivateNote $assignment
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(AssignmentPrivateNote $assignment): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($assignment);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param AssignmentPrivateNote $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(AssignmentPrivateNote $entity, $action)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.private_note.controller.assignment.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'teachers_assignment_private_note_form',
            'Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentPrivateNoteType'
        );
        $builder->setAction($action);

        return $builder->getForm();
    }
}
