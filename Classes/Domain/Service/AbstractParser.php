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
use Neos\DocTools\Domain\Model\ClassReference;
use Neos\DocTools\Domain\Model\CodeExample;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\Exception\ClassLoadingForReflectionFailedException;

/**
 * Abstract Neos.DocTools parser. Extended by target specific
 * parsers to generate reference documentation.
 */
abstract class AbstractParser
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}