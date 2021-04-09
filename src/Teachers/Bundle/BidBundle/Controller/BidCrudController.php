<?php

namespace Teachers\Bundle\BidBundle\Controller;

use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Teachers\Bundle\BidBundle\Entity\Bid;
use Teachers\Bundle\BidBundle\Form\Type\BidType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This controller covers basic CRUD functionality for Bid entity.
 */
class BidCrudController extends AbstractController
{
    /**
     * @Route("/", name="teachers_bid_index")
     * @AclAncestor("teachers_bid_view")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(
            '@TeachersBid/BidCrud/index.html.twig',
            [
                'entity_class' => Bid::class,
            ]
        );
    }

    /**
     * @Route("/view/{id}", name="teachers_bid_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="teachers_bid_view",
     *      type="entity",
     *      class="TeachersBidBundle:Bid",
     *      permission="VIEW"
     * )
     *
     * @param Bid $bid
     *
     * @return Response
     */
    public function viewAction(Bid $bid)
    {
        return $this->render('@TeachersBid/BidCrud/view.html.twig', ['entity' => $bid]);
    }

    /**
     * @Route("/create", name="teachers_bid_create")
     * @Template("TeachersBidBundle:BidCrud:update.html.twig")
     * @Acl(
     *      id="teachers_bid_create",
     *      type="entity",
     *      class="TeachersBidBundle:Bid",
     *      permission="CREATE"
     * )
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $bid = new Bid();
        return $this->update($request, $bid);
    }

    /**
     * @Route("/update/{id}", name="teachers_bid_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="teachers_bid_update",
     *      type="entity",
     *      class="TeachersBidBundle:Bid",
     *      permission="EDIT"
     * )
     * @param Request $request
     * @param Bid $bid
     *
     * @return Response
     */
    public function updateAction(Request $request, Bid $bid)
    {
        return $this->update($request, $bid);
    }

    /**
     * @param Request $request
     * @param Bid $bid
     *
     * @return Response|array
     */
    protected function update(Request $request, Bid $bid)
    {
        $updateResult = $this->get(UpdateHandlerFacade::class)->update(
            $bid,
            $this->createForm(BidType::class, $bid),
            $this->get(TranslatorInterface::class)->trans('teachers.bid.saved_message'),
            $request,
            null,
            'teachers_bid_update'
        );

        return $updateResult;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            UpdateHandlerFacade::class,
            TranslatorInterface::class,
        ]);
    }
}
