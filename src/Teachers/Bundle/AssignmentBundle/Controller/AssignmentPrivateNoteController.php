<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormInterface;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote;

class AssignmentPrivateNoteController extends AbstractController
{
    /**
     * @param \Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote $assignmentPrivateNote
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_private_note_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_assignment_private_note_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:Assignment"
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
     * @param \Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote $assignmentPrivateNote
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
     *      id="teachers_assignment_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     * @param \Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote $assignmentPrivateNote
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
     *      class="TeachersAssignmentBundle:Assignment"
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
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     * @CsrfProtection()
     * @param \Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote $assignment
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
     * @param \Teachers\Bundle\AssignmentBundle\Entity\AssignmentPrivateNote $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(AssignmentPrivateNote $entity, $action)
    {
        /** @var \Oro\Bundle\FormBundle\Model\UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.assignmentprivatenote.controller.assignment.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'teachers_assignment_private_note_form',
            'Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentPrivateNoteType'
        );
        $builder->setAction($action);

        return $builder->getForm();
    }
}
