<?php
declare(strict_types=1);
namespace Neos\DocTools\Eel;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\DocTools\Domain\Service\FusionParser;
use Neos\Eel\ProtectedContextAwareInterface;




class PropTypesHelper implements ProtectedContextAwareInterface, \Stringable, \JsonSerializable
{
    protected ?string $type = null;

    protected bool $isRequired = false;

    public function __construct(string $value = null)
    {
        $this->type = $value;
    }

    public function getAny()
    {
        return new self("mixed");
    }

    public function getBoolean()
    {
        return new self("bool");
    }

    public function getInteger()
    {
        return new self("integer");
    }

    public function getFloat()
    {
        return new self("float");
    }

    public function getString()
    {
        return new self("string");
    }

    public function regex($regularExpression)
    {
        return new self("RegExp");
    }

    public function oneOf(array $values)
    {
        $formattedValues = array_map(fn($value) => FusionParser::formatValue($value), $values);
        return new self(join("|", $formattedValues));
    }

    public function arrayOf($things)
    {
        return new self("Array<" . $things . ">");
    }

    public function anyOf(...$values)
    {
        return new self(join("|", $values));
    }

    public function dataStructure($shape)
    {
        return $this->shape($shape);
    }

    public function shape($shape)
    {
        function parseShape(array $shape): string
        {
            $isArrayLiteral = array_is_list($shape);
            $parsedParts = array_map(function ($name, $value) use ($isArrayLiteral) {
                $value = FusionParser::formatValue($value);
                if (is_array($value)) {
                    $value = parseShape($value);
                }
                return $isArrayLiteral ? $value : $name . ": " . $value;
            }, array_keys($shape), $shape);

            $joinedParts = join(", ", $parsedParts);
            return $isArrayLiteral ? "[$joinedParts]" : "{ $joinedParts }";
        }
        return new self(json_encode($shape));
    }

    public function instanceOf ($type)
    {
        return new self("" . $type);
    }

    public function getFileExists()
    {
        return new self("*existing file*");
    }

    public function getIsRequired()
    {
        $this->isRequired = true;
        return $this;
    }

    public function clearAndGet()
    {
        $result = [
            "type" => $this->type,
            "required" => $this->isRequired,
        ];

        $this->type = null;
        $this->isRequired = false;

        return $result;
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function __toString()
    {
        return (string) $this->type;
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}