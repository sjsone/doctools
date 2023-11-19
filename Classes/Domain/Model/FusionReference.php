<?php
declare(strict_types=1);
namespace Neos\DocTools\Domain\Model;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * @todo document
 */
class FusionReference
{


    /**
     * @param string $title
     * @param string $description
     * @param string $summary
     * @param FusionPropertyDefinition[] $propertyDefinitions
     * @param CodeExample[] $codeExamples
     * @param string $deprecationNote
     */
    public function __construct(
        protected string $title,
        protected string $description,
        protected string $summary,
        protected array $propertyDefinitions,
        protected string $deprecationNote,
    ) {

    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return FusionPropertyDefinition[]
     */
    public function getPropertyDefinitions(): array
    {
        return $this->propertyDefinitions;
    }

    public function getDeprecationNote(): string
    {
        return $this->deprecationNote;
    }
}