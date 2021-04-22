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

class AssignmentController extends AbstractController
{
    /**
     * @param Assignment $assignment
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_view", requirements={"id"="\d+"}, options={"expose"=true})
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
     * @AclAncestor("teachers_assignment_create")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Assignment::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_info", requirements={"id"="\d+"}, options={"expose"=true})
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
     * @Route("/update/{id}", name="teachers_assignment_update", requirements={"id"="\d+"}, options={"expose"=true})
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
        return $this->update($assignment, 'teachers_assignment_update');
    }

    /**
     * @Route("/create", name="teachers_assignment_create", options={"expose"=true})
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
        return $this->update(new Assignment(), 'teachers_assignment_create');
    }

    /**
     * @Route(
     *     "/delete/{id}",
     *     name="teachers_assignment_delete",
     *     requirements={"id"="\d+"},
     *     methods={"DELETE"},
     *     options={"expose"=true}
     * )
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
     * @param \Teachers\Bundle\AssignmentBundle\Entity\Assignment $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(Assignment $entity, $action)
    {
        /** @var \Oro\Bundle\FormBundle\Model\UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.controller.assignment.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'teachers_assignment_form',
            'Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentType'
        );
        $builder->setAction($action);

        return $builder->getForm();
    }
}
