<?php
/**
 * Copyright (c) 2018. Constantin Galbenu <xprt64@gmail.com> Toate drepturile rezervate. All rights reserved.
 */

declare(strict_types=1);

namespace Dudulina\Query\Answerer;

use Dudulina\Query\Answerer;
use Dudulina\Query\AskerResolver;
use Psr\Container\ContainerInterface;

class DefaultAnswerer implements Answerer
{

    /**
     * @var AskerResolver
     */
    private $resolver;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container,
        AskerResolver $resolver
    )
    {
        $this->resolver = $resolver;
        $this->container = $container;
    }

    public function answer($answeredQuestion):void
    {
        foreach ($this->resolver->findAskers($answeredQuestion) as $asker) {
            $this->container->get($asker->getHandlerClass())->{$asker->getMethodName()}($answeredQuestion);
        }
    }
}