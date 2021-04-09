<?php

namespace Teachers\Bundle\UsersBundle\Form;

use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Form provider returns data to template during the CRUD operations
 */
class TeacherGroupFormTemplateDataProvider implements FormTemplateDataProviderInterface
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
            $formAction = $this->router->generate('teachers_group_update', ['id' => $entity->getId()]);
        } else {
            $formAction = $this->router->generate('teachers_group_create');
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'formAction' => $formAction
        ];
    }
}
