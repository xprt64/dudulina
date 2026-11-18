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

    public function __construct(private ContainerInterface $container, private AskerResolver $resolver)
    {
    }

    public function answer($answeredQuestion):void
    {
        foreach ($this->resolver->findAskers($answeredQuestion) as $asker) {
            $this->container->get($asker->getHandlerClass())->{$asker->getMethodName()}($answeredQuestion);
        }
    }
}