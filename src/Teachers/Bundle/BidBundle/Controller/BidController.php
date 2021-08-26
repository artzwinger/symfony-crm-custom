<?php

namespace Teachers\Bundle\BidBundle\Controller;

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
use Teachers\Bundle\BidBundle\Entity\Bid;

class BidController extends AbstractController
{
    /**
     * @param Bid $bid
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_bid_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_bid_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersBidBundle:Bid"
     * )
     * @throws ORMException
     */
    public function viewAction(Bid $bid): array
    {
        /** @var EntityManager $em */
        $roleHelper = $this->get('teachers_users.helper.role');
        if ($roleHelper->isCurrentUserCourseManager() || $roleHelper->isCurrentUserAdmin()) {
            $em = $this->get('doctrine.orm.entity_manager');
            if ($bid->getUnViewed()) {
                $bid->setUnViewed(false);
                $em->persist($bid);
                $em->flush($bid);
            }
        }
        return [
            'entity' => $bid
        ];
    }

    /**
     * @Route(name="teachers_bid_index")
     * @Template("@TeachersBid/Bid/index.html.twig")
     * @AclAncestor("teachers_bid_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Bid::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_bid_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersBid/Bid/widget/info.html.twig")
     * @AclAncestor("teachers_bid_view")
     * @param Bid $bid
     * @return array
     * @throws ORMException
     */
    public function infoAction(Bid $bid): array
    {
        /** @var EntityManager $em */
        $roleHelper = $this->get('teachers_users.helper.role');
        if ($roleHelper->isCurrentUserCourseManager() || $roleHelper->isCurrentUserAdmin()) {
            $em = $this->get('doctrine.orm.entity_manager');
            if ($bid->getUnViewed()) {
                $bid->setUnViewed(false);
                $em->persist($bid);
                $em->flush($bid);
            }
        }
        return [
            'entity' => $bid
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_bid_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersBid/Bid/update.html.twig")
     * @Acl(
     *      id="teachers_bid_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersBidBundle:Bid"
     * )
     * @param Bid $bid
     * @return array|RedirectResponse
     */
    public function updateAction(Bid $bid)
    {
        $result = $this->update($bid, 'teachers_bid_update');
        $result['roleHelper'] = $this->get('teachers_users.helper.role');
        return $result;
    }

    /**
     * @Route("/create", name="teachers_bid_create", options={"expose"=true})
     * @Template("@TeachersBid/Bid/update.html.twig")
     * @Acl(
     *      id="teachers_bid_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersBidBundle:Bid"
     * )
     */
    public function createAction()
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $bid = new Bid();
        $request = $this->get('request_stack')->getCurrentRequest();
        $entityClass = $request->get('entityClass');
        $entityId = $request->get('entityId');
        try {
            if ($entityClass && $entityId) {
                $entityClass = str_replace('_', '\\', $entityClass);
                $repository = $em->getRepository($entityClass);
                /** @var Assignment $assignment */
                $assignment = $repository->find($entityId);
                if (empty($assignment)) {
                    throw new EntityNotFoundException();
                }
                $bid->setAssignment($assignment);
            }
        } catch (Exception $e) {
        }
        $result = $this->update($bid, 'teachers_bid_create');
        $result['roleHelper'] = $this->get('teachers_users.helper.role');
        return $result;
    }

    /**
     * @Route("/delete/{id}", name="teachers_bid_delete", requirements={"id"="\d+"}, methods={"DELETE"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_bid_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersBidBundle:Bid"
     * )
     * @CsrfProtection()
     * @param Bid $bid
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Bid $bid): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($bid);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Bid $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(Bid $entity, $action)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.bid.controller.bid.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder('teachers_bid_form', 'Teachers\Bundle\BidBundle\Form\Type\BidType');
        $builder->setAction($action);

        return $builder->getForm();
    }
}
