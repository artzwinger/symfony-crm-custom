<?php

namespace Teachers\Bundle\UsersBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;
use Teachers\Bundle\UsersBundle\Form\Handler\TeacherGroupHandler;

/**
 * @RouteResource("assignment")
 * @NamePrefix("teachers_api_")
 */
class TeacherGroupController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET item
     *
     * @param int $id
     * @Get(requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Get assignment item",
     *      resource=true
     * )
     * @AclAncestor("teachers_group_view")
     * @return Response
     */
    public function getAction(int $id): Response
    {
        return $this->handleGetRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('teachers_users.teacher_group.manager.api');
    }

    /**
     * @return TeacherGroupHandler
     */
    public function getFormHandler(): TeacherGroupHandler
    {
        return $this->get('teachers_users.teacher_group.form.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('teachers_usersembedded_form');
    }
}
