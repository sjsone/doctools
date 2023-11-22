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


use Neos\DocTools\Domain\Model\FusionPropertyDefinition;
use Neos\DocTools\Domain\Model\FusionReference;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\Exception\ClassLoadingForReflectionFailedException;
use PHPUnit\Framework\Error\Deprecated;
use Neos\Eel\EelEvaluatorInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\Utility;

/**
 * Neos.DocTools parser for classes. Extended by target specific
 * parsers to generate reference documentation.
 */
class FusionParser extends AbstractParser
{
    /**
     * @Flow\Inject(lazy=false)
     * @var EelEvaluatorInterface
     */
    protected $eelEvaluator;


    protected function evaluateEelExpression(string $expression)
    {
        $context = [
            "PropTypes" => "Neos\DocTools\Eel\PropTypesHelper"
        ];

        return Utility::evaluateEelExpression("\${" . $expression . "}", $this->eelEvaluator, [], $context);
    }

    final public function parse(array $prototypeDefinition, string $prototypeName): mixed
    {
        if (!isset($prototypeDefinition['__meta']['doc'])) {
            return null;
        }

        ['summary' => $summary, 'description' => $description] = $this->parseMetaDoc($prototypeDefinition);
        // \Neos\Flow\var_dump($prototypeName);
        $propertyDefinitions = $this->buildFusionPropertyDefinitions($prototypeDefinition);

        $deprecationNote = '';
        if (isset($prototypeDefinition['__meta']['deprecated']) && is_string($prototypeDefinition['__meta']['deprecated'])) {
            $deprecationNote = $prototypeDefinition['__meta']['deprecated'];
        }


        return new FusionReference($prototypeName, $description, $summary, $propertyDefinitions, $deprecationNote);
    }

    protected function buildFusionPropertyDefinitions(array $prototypeDefinition): array
    {
        $propertyDefinitions = [];
        if (!isset($prototypeDefinition['__meta']['propTypes'])) {
            return $propertyDefinitions;
        }
        foreach ($prototypeDefinition['__meta']['propTypes'] as $propTypeName => $propType) {
            if ($propTypeName === "__meta") {
                continue;
            }
            $propertyDefinitions[] = $this->buildFusionPropertyDefinition($propTypeName, $propType, $prototypeDefinition);
        }

        if (isset($prototypeDefinition['__meta']['propTypes']['__meta']['meta'])) {
            $metaProperties = $prototypeDefinition['__meta']['propTypes']['__meta']['meta'];
            foreach ($metaProperties as $propTypeName => $propType) {
                $propertyDefinitions[] = $this->buildFusionPropertyDefinition($propTypeName, $propType, $prototypeDefinition, true);
            }
        }

        return $propertyDefinitions;
    }

    protected function buildFusionPropertyDefinition(string $propTypeName, array $propType, array $prototypeDefinition, bool $isMetaProperty = false): FusionPropertyDefinition
    {
        ['summary' => $summary, 'description' => $description] = $this->parseMetaDoc($propType);

        $ret = $this->evaluateEelExpression($propType['__eelExpression']);

        ['type' => $type, 'required' => $required] = $ret->clearAndGet();

        $default = null;
        if (!$isMetaProperty && isset($prototypeDefinition[$propTypeName])) {
            $default = $this->parseDefaultValue($prototypeDefinition[$propTypeName]);
        }

        return new FusionPropertyDefinition(($isMetaProperty ? '@' : '') . $propTypeName, $required, $type ?? '', $default, $summary, $description);
    }

    protected function parseDefaultValue(mixed $prop)
    {
        $formattedProp = self::formatValue($prop);
        if (!is_array($formattedProp)) {
            return (string) $formattedProp;
        }

        if ($prop['__eelExpression'] !== null) {
            return "\${" . $prop['__eelExpression'] . "}";
        }

        if ($prop['__objectType'] !== null) {
            return $prop['__objectType'];
        }

        return null;
    }

    protected function parseMetaDoc(mixed $definition)
    {
        $doc = [
            'summary' => null,
            'description' => null
        ];

        if (!isset($definition['__meta'])) {
            return $doc;
        }
        if (is_string($definition['__meta']['doc'])) {
            return ['summary' => $definition['__meta']['doc'], 'description' => null];
        }
        if (isset($definition['__meta']['doc']['summary'])) {
            $doc['summary'] = $definition['__meta']['doc']['summary'];
        }
        if (isset($definition['__meta']['doc']['description'])) {
            $doc['description'] = $definition['__meta']['doc']['description'];
        }
        return $doc;
    }

    public static function formatValue($value)
    {
        if (is_string($value)) {
            $value = "'$value'";
        }
        if (is_bool($value)) {
            $value = $value ? "true" : "false";
        }
        if (is_null($value)) {
            $value = "null";
        }
        return $value;
    }
}