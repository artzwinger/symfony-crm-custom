<?php

namespace Teachers\Bundle\ApplicationBundle\Form;

use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Form provider returns data to template during the CRUD operations
 */
class ApplicationFormTemplateDataProvider implements FormTemplateDataProviderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($entity, FormInterface $form, Request $request): array
    {
        if ($entity->getId()) {
            $formAction = $this->router->generate('teachers_application_update', ['id' => $entity->getId()]);
        } else {
            $formAction = $this->router->generate('teachers_application_create');
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'formAction' => $formAction
        ];
    }
}
