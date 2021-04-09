<?php

namespace Teachers\Bundle\ApplicationBundle\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class ApplicationHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param ManagerRegistry $registry
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, ManagerRegistry $registry)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->registry = $registry;
    }

    /**
     * @param Application $entity
     * @return bool
     */
    public function process(Application $entity): bool
    {
        $this->getForm()->setData($entity);
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);
            if ($this->getForm()->isValid()) {
                $manager = $this->registry->getManagerForClass(Application::class);
                $manager->persist($entity);
                $manager->flush();
                return true;
            }
        }
        return false;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
