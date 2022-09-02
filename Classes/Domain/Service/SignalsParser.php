<?php
declare(strict_types=1);
namespace Neos\DocTools\Domain\Service;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\DocTools\Domain\Model\ArgumentDefinition;
use Neos\DocTools\Domain\Model\CodeExample;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Annotations\Signal;
use Neos\Flow\Reflection\MethodReflection;
use Neos\Flow\Reflection\ReflectionService;

/**
 * Neos.DocTools parser for signals in classes
 */
class SignalsParser extends AbstractClassParser
{
    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    protected function parseTitle(): string
    {
        return substr($this->className, strrpos($this->className, '\\') + 1) . ' (``' . $this->className . '``)';
    }

    /**
     * @throws \ReflectionException
     */
    protected function parseDescription(): string
    {
        $description = 'This class contains the following signals.' . chr(10) . chr(10);
        foreach ($this->reflectionService->getMethodsAnnotatedWith($this->className, Signal::class) as $methodName) {
            $methodReflection = new MethodReflection($this->className . '::' . $methodName);
            $signalName = lcfirst(preg_replace('/^emit/', '', $methodReflection->getName()));
            $description .= $signalName;
            $description .= chr(10) . str_repeat('^', strlen($signalName));
            $description .= chr(10) . chr(10) . $methodReflection->getDescription() . chr(10) . chr(10);
        }

        return $description;
    }

    /**
     * @return ArgumentDefinition[]
     */
    protected function parseArgumentDefinitions(): array
    {
        return [];
    }

    /**
     * @return CodeExample[]
     */
    protected function parseCodeExamples(): array
    {
        return [];
    }
}
