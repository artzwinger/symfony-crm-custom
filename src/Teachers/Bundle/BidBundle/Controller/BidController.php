<?php

namespace Teachers\Bundle\BidBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\BidBundle\Entity\Repository\BidRepository;
use Teachers\Bundle\BidBundle\Entity\Bid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This controller covers widget-related functionality for Bid entity.
 */
class BidController extends Controller
{
    /**
     * @Route(
     *     "/widget/sidebar-bids/{perPage}",
     *     name="teachers_bid_widget_sidebar_bids",
     *     defaults={"perPage" = 10},
     *     requirements={"perPage"="\d+"}
     * )
     * @AclAncestor("teachers_bid_view")
     *
     * @param int $perPage
     *
     * @return Response
     */
    public function bidsWidgetAction(int $perPage): Response
    {
        /** @var BidRepository $bidRepository */
        $bidRepository = $this->getDoctrine()->getRepository(Bid::class);
        $userId = $this->getUser()->getId();
        $bids = $bidRepository->getBidsAssignedTo($userId, $perPage);

        return $this->render('@TeachersBid/Bid/widget/bidsWidget.html.twig', ['bids' => $bids]);
    }

    /**
     * @Route("/widget/info/{id}", name="teachers_bid_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("teachers_bid_view")
     * @Template("TeachersBidBundle:Bid/widget:info.html.twig")
     *
     * @param Request $request
     * @param Bid $entity
     *
     * @return array
     */
    public function infoAction(Request $request, Bid $entity): array
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
     * This action is used to render the list of bids associated with the given entity
     * on the view page of this entity
     *
     * @Route(
     *      "/activity/view/{entityClass}/{entityId}",
     *      name="teachers_bid_activity_view",
     *      requirements={"entityClass"="\w+", "entityId"="\d+"}
     * )
     *
     * @AclAncestor("teachers_bid_view")
     *
     * @param string $entityClass
     * @param int $entityId
     *
     * @return Response
     */
    public function activityAction(string $entityClass, int $entityId): Response
    {
        return $this->render(
            'TeachersBidBundle:Bid:activity.html.twig',
            [
                'entity' => $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId),
            ]
        );
    }

    /**
     * @Route("/user/{user}", name="teachers_bid_user_bids", requirements={"user"="\d+"})
     * @AclAncestor("teachers_bid_view")
     *
     * @param User $user
     *
     * @return Response
     */
    public function userBidsAction(User $user): Response
    {
        return $this->render('@TeachersBid/Bid/widget/userBids.html.twig', ['entity' => $user]);
    }

    /**
     * @Route("/my", name="teachers_bid_my_bids")
     * @AclAncestor("teachers_bid_view")
     *
     * @return Response
     */
    public function myBidsAction(): Response
    {
        return $this->render('@TeachersBid/Bid/myBids.html.twig');
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
