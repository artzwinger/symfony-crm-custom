<?php

namespace Teachers\Bundle\ApplicationBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;
use Teachers\Bundle\ApplicationBundle\Form\Handler\ApplicationHandler;

/**
 * @RouteResource("application")
 * @NamePrefix("teachers_api_")
 */
class ApplicationController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET item
     *
     * @param int $id
     * @Get(requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Get application item",
     *      resource=true
     * )
     * @AclAncestor("teachers_application_view")
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
        return $this->get('teachers_application.application.manager.api');
    }

    /**
     * @return ApplicationHandler
     */
    public function getFormHandler(): ApplicationHandler
    {
        return $this->get('teachers_application.application.form.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('teachers_application.embedded_form');
    }
}
