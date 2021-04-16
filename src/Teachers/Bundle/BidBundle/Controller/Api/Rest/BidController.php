<?php

namespace Teachers\Bundle\BidBundle\Controller\Api\Rest;

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
 * @RouteResource("bid")
 * @NamePrefix("teachers_api_")
 */
class BidController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET item
     *
     * @param int $id
     * @Get(requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Get bid item",
     *      resource=true
     * )
     * @AclAncestor("teachers_bid_view")
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
        return $this->get('teachers_bid.bid.manager.api');
    }

    /**
     * @return AssignmentHandler
     */
    public function getFormHandler(): AssignmentHandler
    {
        return $this->get('teachers_bid.bid.form.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('teachers_bid.embedded_form');
    }
}
