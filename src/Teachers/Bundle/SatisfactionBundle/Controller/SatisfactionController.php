<?php

namespace Teachers\Bundle\SatisfactionBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\SatisfactionBundle\Entity\Repository\SatisfactionRepository;
use Teachers\Bundle\SatisfactionBundle\Entity\Satisfaction;

/**
 * This controller covers widget-related functionality for Satisfaction entity.
 */
class SatisfactionController extends Controller
{
    /**
     * @Route(
     *     "/widget/sidebar-satisfactions/{perPage}",
     *     name="teachers_satisfaction_widget_sidebar_satisfactions",
     *     defaults={"perPage" = 10},
     *     requirements={"perPage"="\d+"}
     * )
     * @AclAncestor("teachers_satisfaction_view")
     *
     * @param int $perPage
     *
     * @return Response
     */
    public function satisfactionsWidgetAction(int $perPage): Response
    {
        /** @var SatisfactionRepository $satisfactionRepository */
        $satisfactionRepository = $this->getDoctrine()->getRepository(Satisfaction::class);
        $userId = $this->getUser()->getId();
        $satisfactions = $satisfactionRepository->getSatisfactionsAssignedTo($userId, $perPage);

        return $this->render('@TeachersSatisfaction/Satisfaction/widget/satisfactionsWidget.html.twig', ['satisfactions' => $satisfactions]);
    }

    /**
     * @Route("/widget/info/{id}", name="teachers_satisfaction_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("teachers_satisfaction_view")
     * @Template("TeachersSatisfactionBundle:Satisfaction/widget:info.html.twig")
     *
     * @param Request $request
     * @param Satisfaction $entity
     *
     * @return array
     */
    public function infoAction(Request $request, Satisfaction $entity): array
    {
        $targetEntity = $this->getTargetEntity($request);
        $renderContexts = null !== $targetEntity;

        return [
            'entity' => $entity,
            'target' => $targetEntity,
            'renderContexts' => $renderContexts,
        ];
    }

    /**
     * This action is used to render the list of satisfactions associated with the given entity
     * on the view page of this entity
     *
     * @Route(
     *      "/activity/view/{entityClass}/{entityId}",
     *      name="teachers_satisfaction_activity_view",
     *      requirements={"entityClass"="\w+", "entityId"="\d+"}
     * )
     *
     * @AclAncestor("teachers_satisfaction_view")
     *
     * @param string $entityClass
     * @param int $entityId
     *
     * @return Response
     */
    public function activityAction(string $entityClass, int $entityId): Response
    {
        return $this->render(
            'TeachersSatisfactionBundle:Satisfaction:activity.html.twig',
            [
                'entity' => $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId),
            ]
        );
    }

    /**
     * @Route("/user/{user}", name="teachers_satisfaction_user_satisfactions", requirements={"user"="\d+"})
     * @AclAncestor("teachers_satisfaction_view")
     *
     * @param User $user
     *
     * @return Response
     */
    public function userSatisfactionsAction(User $user): Response
    {
        return $this->render('@TeachersSatisfaction/Satisfaction/widget/userSatisfactions.html.twig', ['entity' => $user]);
    }

    /**
     * @Route("/my", name="teachers_satisfaction_my_satisfactions")
     * @AclAncestor("teachers_satisfaction_view")
     *
     * @return Response
     */
    public function mySatisfactionsAction(): Response
    {
        return $this->render('@TeachersSatisfaction/Satisfaction/mySatisfactions.html.twig');
    }

    /**
     * Get target entity
     *
     * @param Request $request
     *
     * @return object|null
     */
    protected function getTargetEntity(Request $request)
    {
        $entityRoutingHelper = $this->get('oro_entity.routing_helper');
        $targetEntityClass = $entityRoutingHelper->getEntityClassName($request, 'targetActivityClass');
        $targetEntityId = $entityRoutingHelper->getEntityId($request, 'targetActivityId');
        if (!$targetEntityClass || !$targetEntityId) {
            return null;
        }

        return $entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
    }
}
