<?php

namespace Teachers\Bundle\AssignmentBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;
use Teachers\Bundle\AssignmentBundle\Form\Handler\AssignmentHandler;

/**
 * @RouteResource("assignment")
 * @NamePrefix("teachers_api_")
 */
class AssignmentController extends RestController implements ClassResourceInterface
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
     * @AclAncestor("teachers_assignment_view")
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
        return $this->get('teachers_assignment.assignment.manager.api');
    }

    /**
     * @return AssignmentHandler
     */
    public function getFormHandler(): AssignmentHandler
    {
        return $this->get('teachers_assignment.assignment.form.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('teachers_assignment.embedded_form');
    }
}
