<?php

namespace Teachers\Bundle\ApplicationBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class ValidateReCaptchaToken implements ProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ReCaptcha
     */
    private $reCaptcha;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param LoggerInterface $logger
     * @param RequestStack $requestStack
     * @param ReCaptcha $reCaptcha
     */
    public function __construct(
        LoggerInterface $logger,
        RequestStack $requestStack,
        ReCaptcha $reCaptcha
    )
    {
        $this->logger = $logger;
        $this->reCaptcha = $reCaptcha;
        $this->requestStack = $requestStack;
    }

    /**
     * @throws HttpExceptionInterface
     */
    public function process(ContextInterface $context)
    {
        /** @var Application $application */
        $application = $context->getResult();
        if (!$application instanceof Application) {
            return;
        }
        $request = $this->requestStack->getCurrentRequest();
        $this->reCaptcha->setExpectedHostname($request->getHost());
        $result = $this->reCaptcha->verify($application->getReCaptchaToken(), $request->getClientIp());
        if (!$result->isSuccess()) {
            $this->logger->error('ReCaptcha token is not valid', [
                'application_email' => $application->getEmail(),
                'recaptcha_errors' => $result->getErrorCodes()
            ]);
            throw new BadRequestHttpException('ReCaptcha token is not valid');
        }
    }
}
