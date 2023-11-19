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
use Neos\DocTools\Domain\Model\FusionPropertyDefinition;
use Neos\DocTools\Domain\Model\FusionReference;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\Exception\ClassLoadingForReflectionFailedException;
use PHPUnit\Framework\Error\Deprecated;

/**
 * Neos.DocTools parser for classes. Extended by target specific
 * parsers to generate reference documentation.
 */
class FusionParser extends AbstractParser
{
    final public function parse(array $prototypeDefinition, string $prototypeName): mixed
    {
        if (!isset($prototypeDefinition['__meta']['doc'])) {
            return null;
        }
        
        ['summary' => $summary, 'description' => $description] = $this->parseMetaDoc($prototypeDefinition);
        // \Neos\Flow\var_dump($prototypeName);
        $propertyDefinitions = $this->parseFusionPropertyDefinitions($prototypeDefinition);

        $deprecationNote = '';
        if (isset($prototypeDefinition['__meta']['deprecated']) && is_string($prototypeDefinition['__meta']['deprecated'])) {
            $deprecationNote = $prototypeDefinition['__meta']['deprecated'];
        }


        return new FusionReference($prototypeName, $description, $summary, $propertyDefinitions, $deprecationNote);
    }

    protected function parseFusionPropertyDefinitions(array $prototypeDefinition): array
    {
        $propertyDefinitions = [];
        if(!isset($prototypeDefinition['__meta']['propTypes'])) {
            return $propertyDefinitions;
        }
        foreach ($prototypeDefinition['__meta']['propTypes'] as $propTypeName => $propType) {
            if($propTypeName === "__meta") {
                // TODO: handle @meta proptypes
                continue;
            } 
            $propertyDefinitions[] = $this->parseFusionPropertyDefinition($propTypeName, $propType, $prototypeDefinition);
        }

        return $propertyDefinitions;
    }

    protected function parseFusionPropertyDefinition(string $propTypeName, array $propType, array $prototypeDefinition): FusionPropertyDefinition
    {
        ['summary' => $summary, 'description' => $description] = $this->parseMetaDoc($propType);
        $required = str_ends_with($propType['__eelExpression'], ".isRequired");

        $default = null;
        if(isset($prototypeDefinition[$propTypeName])) {
            $default = $this->parseDefaultValue($prototypeDefinition[$propTypeName]);
        }

        return new FusionPropertyDefinition($propTypeName, $required, '', $default, $summary, $description);
    }

    protected function parseDefaultValue(mixed $prop) {
        if(is_string($prop)) {
            return $prop;
        }
        // \Neos\Flow\var_dump($prop);
        if( $prop['__eelExpression'] !== null) {
            return "\${".$prop['__eelExpression']."}";
        }

        return null;
    }

    protected function parseMetaDoc(mixed $definition)
    {
        // \Neos\Flow\var_dump($definition);
        if (is_string($definition['__meta']['doc'])) {
            return ['summary' => $definition['__meta']['doc'], 'description' => null];
        }

        $doc = [
            'summary' => null,
            'description' => null
        ];

        if (isset($definition['__meta']['doc']['summary'])) {
            $doc['summary'] = $definition['__meta']['doc']['summary'];
        }
        if (isset($definition['__meta']['doc']['description'])) {
            $doc['description'] = $definition['__meta']['doc']['description'];
        }
        return $doc;
    }
}