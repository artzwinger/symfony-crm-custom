<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

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
     * @throws ORMException
     */
    public function viewAction(Assignment $assignment): array
    {
        $bids = $assignment->getBids();
        /** @var EntityManager $em */
        $roleHelper = $this->get('teachers_users.helper.role');
        if ($roleHelper->isCurrentUserCourseManager() || $roleHelper->isCurrentUserAdmin()) {
            $em = $this->get('doctrine.orm.entity_manager');
            foreach ($bids as $bid) {
                $bid->setUnViewed(false);
                $em->persist($bid);
                $em->flush($bid);
            }
        }
        return [
            'is_user_teacher' => $this->get('teachers_users.helper.role')->isCurrentUserTeacher(),
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
            'entity' => $assignment,
            'roleHelper' => $this->get('teachers_users.helper.role'),
            'is_user_teacher' => $this->get('teachers_users.helper.role')->isCurrentUserTeacher(),
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
        $result = $this->update($assignment);
        $result['formAction'] = $this->generateUrl('teachers_assignment_update', [
            'id' => $assignment->getId()
        ]);

        return $result;
    }

    /**
     * @Route("/getuserinfo/{id}", name="teachers_assignment_getuserinfo", requirements={"id"="\d+"}, options={"expose"=true})
     * @AclAncestor("teachers_assignment_edit")
     * @param User $user
     * @return JsonResponse
     */
    public function getUserInfo(User $user): JsonResponse
    {
        $data = [
            'firstName' => '',
            'lastName' => ''
        ];
        if ($user->getId()) {
            $data = [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getFirstName()
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/create_from_application/{applicationId}",
     *     name="teachers_assignment_create_from_app",
     *     requirements={"applicationId"="\d+"},
     *     options={"expose"=true})
     * @ParamConverter(name="application", options={"id": "applicationId"})
     * @Template("@TeachersAssignment/Assignment/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     * @throws ORMException
     */
    public function createFromApplication(Application $application)
    {
        $assignment = new Assignment();
        $canCreateAssignment = !$application->getAssignment();

        if ($canCreateAssignment) {
            $this->setNewAssignmentCourseManager($assignment);
            $this->populateApplicationDataToAssignment($application, $assignment);
        }

        $result = $this->update($assignment);
        $result['cannotCreateAssignment'] = !$canCreateAssignment;
        $result['formAction'] = $this->generateUrl('teachers_assignment_create_from_app', [
            'applicationId' => $application->getId()
        ]);

        if ($assignment->getId()) {
            $application->setAssignment($assignment);
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($application);
            $em->flush($application);
        }

        return $result;
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
        $assignment = new Assignment();
        $this->setNewAssignmentCourseManager($assignment);
        $result = $this->update($assignment);

        if (is_array($result)) {
            $result['cannotCreateAssignment'] = false;
            $result['formAction'] = $this->generateUrl('teachers_assignment_create');
        }

        return $result;
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
     * @param Assignment $entity
     * @return RedirectResponse|array
     */
    private function update(Assignment $entity)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $factory = $this->get('form.factory');
        $form = $factory->create('Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentType');
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.controller.assignment.saved.message')
        );
    }

    private function setNewAssignmentCourseManager(Assignment $assignment)
    {
        if ($this->get('teachers_users.helper.role')->isCurrentUserCourseManager()) {
            $roleRepository = $this->get('doctrine.orm.entity_manager')
                ->getRepository(Role::class);
            $role = $roleRepository->findOneBy([
                'role' => User::ROLE_ADMINISTRATOR
            ]);
            if ($role) {
                $adminUser = $roleRepository->getFirstMatchedUser($role);
                if ($adminUser) {
                    $assignment->setCourseManager($adminUser);
                }
            }
        }
    }

    private function populateApplicationDataToAssignment(Application $application, Assignment $assignment)
    {
        $assignment->setApplication($application);
        $assignment->setTerm($application->getTerm());
        $assignment->setRep($application->getRep());
        $assignment->setFirstName($application->getFirstName());
        $assignment->setLastName($application->getLastName());
        $assignment->setCourseName($application->getCourseName());
        $assignment->setCoursePrefixes($application->getCoursePrefixes());
        $assignment->setDescription($application->getDescription());
        $assignment->setWorkToday($application->getWorkToday());
        $assignment->setDueDate($application->getDueDate());
        $assignment->setClassStartDate($application->getClassStartDate());
        $assignment->setUserLogin($application->getUserLogin());
        $assignment->setUserPassword($application->getUserPassword());
        $assignment->setCourseUrl($application->getCourseUrl());
        $assignment->setInstructions($application->getInstructions());
        $assignment->setAmountDueToday($application->getAmountDueToday());
        if ($application->getStudent()) {
            $assignment->setStudent($application->getStudent());
        }
        if ($studentContact = $application->getStudentContact()) {
            $assignment->setStudentContact($studentContact);
        }
        if ($studentAccount = $application->getStudentAccount()) {
            $assignment->setStudentAccount($studentAccount);
        }
    }
}
