<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

class AssignmentController extends AbstractController
{
    /**
     * @param Assignment $assignment
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="teachers_assignment_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     */
    public function viewAction(Assignment $assignment): array
    {
        return [
            'entity' => $assignment
        ];
    }

    /**
     * @Route(name="teachers_assignment_index")
     * @Template("@TeachersAssignment/Assignment/index.html.twig")
     * @AclAncestor("teachers_assignment_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Assignment::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_info", requirements={"id"="\d+"})
     * @Template("@TeachersAssignment/Assignment/widget/info.html.twig")
     * @AclAncestor("teachers_assignment_view")
     * @param Assignment $assignment
     * @return array
     */
    public function infoAction(Assignment $assignment): array
    {
        return [
            'entity' => $assignment
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_assignment_update", requirements={"id"="\d+"})
     * @Template("@TeachersAssignment/Assignment/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     * @param Assignment $assignment
     * @return array|RedirectResponse
     */
    public function updateAction(Assignment $assignment)
    {
        return $this->update($assignment);
    }

    /**
     * @Route("/create", name="teachers_assignment_create")
     * @Template("@TeachersAssignment/Assignment/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     */
    public function createAction()
    {
        return $this->update(new Assignment());
    }

    /**
     * @Route("/delete/{id}", name="teachers_assignment_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     * @Acl(
     *      id="teachers_assignment_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     * @CsrfProtection()
     * @param Assignment $assignment
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Assignment $assignment): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($assignment);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Assignment $assignment
     * @return array|RedirectResponse
     */
    protected function update(Assignment $assignment)
    {
        $handler = $this->get('teachers_assignment.assignment.form.handler');

        if ($handler->process($assignment)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('teachers.assignment.assignment.entity.saved')
            );

            return $this->get('oro_ui.router')->redirect($assignment);
        }

        return [
            'entity' => $assignment,
            'form' => $handler->getForm()->createView()
        ];
    }
}
