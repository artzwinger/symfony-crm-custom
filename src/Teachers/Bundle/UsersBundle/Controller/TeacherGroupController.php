<?php

namespace Teachers\Bundle\UsersBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormInterface;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroup;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Component\Routing\Annotation\Route;

class TeacherGroupController extends AbstractController
{
    /**
     * @param TeacherGroup $teacherGroup
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_group_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="teachers_group_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersUsersBundle:TeacherGroup"
     * )
     */
    public function viewAction(TeacherGroup $teacherGroup): array
    {
        return [
            'entity' => $teacherGroup
        ];
    }

    /**
     * @Route(name="teachers_group_index")
     * @Template("@TeachersUsers/TeacherGroup/index.html.twig")
     * @AclAncestor("teachers_group_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => TeacherGroup::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_group_info", requirements={"id"="\d+"})
     * @Template("@TeachersUsers/TeacherGroup/widget/info.html.twig")
     * @AclAncestor("teachers_group_view")
     * @param TeacherGroup $teacherGroup
     * @return array
     */
    public function infoAction(TeacherGroup $teacherGroup): array
    {
        return [
            'entity' => $teacherGroup
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_group_update", requirements={"id"="\d+"})
     * @Template("@TeachersUsers/TeacherGroup/update.html.twig")
     * @Acl(
     *      id="teachers_group_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersUsersBundle:TeacherGroup"
     * )
     * @param TeacherGroup $teacherGroup
     * @return array|RedirectResponse
     */
    public function updateAction(TeacherGroup $teacherGroup)
    {
        return $this->update($teacherGroup, 'teachers_group_update');
    }

    /**
     * @Route("/create", name="teachers_group_create", options={"expose": true})
     * @Template("@TeachersUsers/TeacherGroup/update.html.twig")
     * @Acl(
     *      id="teachers_group_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersUsersBundle:TeacherGroup"
     * )
     */
    public function createAction()
    {
        return $this->update(new TeacherGroup(), 'teachers_group_create');
    }

    /**
     * @Route("/delete/{id}", name="teachers_group_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     * @Acl(
     *      id="teachers_group_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersUsersBundle:TeacherGroup"
     * )
     * @CsrfProtection()
     * @param TeacherGroup $teacherGroup
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(TeacherGroup $teacherGroup): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($teacherGroup);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param TeacherGroup $teacherGroup
     * @param $action
     * @return array|RedirectResponse
     */
    protected function update(TeacherGroup $teacherGroup, $action)
    {
        /** @var \Oro\Bundle\FormBundle\Model\UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $teacherGroup,
            $form,
            $this->get('translator')->trans('teachers.users.teacher_group.entity.saved')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'teachers_teacher_group_form',
            'Teachers\Bundle\UsersBundle\Form\Type\TeacherGroupType'
        );
        $builder->setAction($action);

        return $builder->getForm();
    }
}
