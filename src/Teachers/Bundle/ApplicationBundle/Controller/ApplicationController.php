<?php

namespace Teachers\Bundle\ApplicationBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
    /**
     * @param Application $application
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_application_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="teachers_application_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersApplicationBundle:Application"
     * )
     */
    public function viewAction(Application $application): array
    {
        return [
            'entity' => $application
        ];
    }

    /**
     * @Route(name="teachers_application_index")
     * @Template("@TeachersApplication/Application/index.html.twig")
     * @AclAncestor("teachers_application_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Application::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_application_info", requirements={"id"="\d+"})
     * @Template("@TeachersApplication/Application/widget/info.html.twig")
     * @AclAncestor("teachers_application_view")
     * @param Application $application
     * @return array
     */
    public function infoAction(Application $application): array
    {
        $attachmentProvider = $this->get('oro_attachment.provider.attachment');
        $attachment = $attachmentProvider->getAttachmentInfo($application);

        return [
            'entity' => $application,
            'attachment' => $attachment
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_application_update", requirements={"id"="\d+"})
     * @Template("@TeachersApplication/Application/update.html.twig")
     * @Acl(
     *      id="teachers_application_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersApplicationBundle:Application"
     * )
     * @param Application $application
     * @return array|RedirectResponse
     */
    public function updateAction(Application $application)
    {
        return $this->update($application);
    }

    /**
     * @Route("/create", name="teachers_application_create")
     * @Template("@TeachersApplication/Application/update.html.twig")
     * @Acl(
     *      id="teachers_application_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersApplicationBundle:Application"
     * )
     */
    public function createAction()
    {
        return $this->update(new Application());
    }

    /**
     * @Route("/delete/{id}", name="teachers_application_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     * @Acl(
     *      id="teachers_application_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersApplicationBundle:Application"
     * )
     * @CsrfProtection()
     * @param Application $application
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Application $application): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($application);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @param Application $application
     * @return array|RedirectResponse
     */
    protected function update(Application $application)
    {
        $handler = $this->get('teachers_application.application.form.handler');

        if ($handler->process($application)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('teachers.application.entity.saved')
            );

            return $this->get('oro_ui.router')->redirect($application);
        }

        return [
            'entity' => $application,
            'form' => $handler->getForm()->createView()
        ];
    }
}
