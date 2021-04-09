<?php

namespace Teachers\Bundle\SatisfactionBundle\Controller;

use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Teachers\Bundle\SatisfactionBundle\Entity\Satisfaction;
use Teachers\Bundle\SatisfactionBundle\Form\Type\SatisfactionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This controller covers basic CRUD functionality for Satisfaction entity.
 */
class SatisfactionCrudController extends AbstractController
{
    /**
     * @Route("/", name="teachers_satisfaction_index")
     * @AclAncestor("teachers_satisfaction_view")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(
            '@TeachersSatisfaction/SatisfactionCrud/index.html.twig',
            [
                'entity_class' => Satisfaction::class,
            ]
        );
    }

    /**
     * @Route("/view/{id}", name="teachers_satisfaction_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="teachers_satisfaction_view",
     *      type="entity",
     *      class="TeachersSatisfactionBundle:Satisfaction",
     *      permission="VIEW"
     * )
     *
     * @param Satisfaction $satisfaction
     *
     * @return Response
     */
    public function viewAction(Satisfaction $satisfaction)
    {
        return $this->render('@TeachersSatisfaction/SatisfactionCrud/view.html.twig', ['entity' => $satisfaction]);
    }

    /**
     * @Route("/create", name="teachers_satisfaction_create")
     * @Template("TeachersSatisfactionBundle:SatisfactionCrud:update.html.twig")
     * @Acl(
     *      id="teachers_satisfaction_create",
     *      type="entity",
     *      class="TeachersSatisfactionBundle:Satisfaction",
     *      permission="CREATE"
     * )
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $satisfaction = new Satisfaction();
        return $this->update($request, $satisfaction);
    }

    /**
     * @Route("/update/{id}", name="teachers_satisfaction_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="teachers_satisfaction_update",
     *      type="entity",
     *      class="TeachersSatisfactionBundle:Satisfaction",
     *      permission="EDIT"
     * )
     * @param Request $request
     * @param Satisfaction $satisfaction
     *
     * @return Response
     */
    public function updateAction(Request $request, Satisfaction $satisfaction)
    {
        return $this->update($request, $satisfaction);
    }

    /**
     * @param Request $request
     * @param Satisfaction $satisfaction
     *
     * @return Response|array
     */
    protected function update(Request $request, Satisfaction $satisfaction)
    {
        $updateResult = $this->get(UpdateHandlerFacade::class)->update(
            $satisfaction,
            $this->createForm(SatisfactionType::class, $satisfaction),
            $this->get(TranslatorInterface::class)->trans('teachers.satisfaction.saved_message'),
            $request,
            null,
            'teachers_satisfaction_update'
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
