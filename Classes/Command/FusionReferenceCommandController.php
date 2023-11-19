<?php
declare(strict_types=1);
namespace Neos\DocTools\Command;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\DocTools\Domain\Model\FusionReference;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\Command;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Mvc\Exception\CommandException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\FluidAdaptor\Exception;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\Fusion\Core\FusionSourceCode;
use Neos\Fusion\Core\FusionSourceCodeCollection;
use Neos\Fusion\Core\Parser;

/**
 * "Fusion Reference" command controller for the Documentation package.
 *
 * Used to create reference documentation for Fusion Prototypes.
 *
 * @Flow\Scope("singleton")
 */
class FusionReferenceCommandController extends CommandController
{


    protected string $defaultTemplatePath = 'resource://Neos.DocTools/Private/Templates/FusionReferenceTemplate.txt';

    protected array $settings;


    /**
     * @Flow\Inject
     * @var Parser
     */
    protected $fusionParser;

    public function injectSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * Renders reference documentation from source code.
     *
     * @param string|null $reference to render. If not specified all configured references will be rendered
     * @return void
     * @throws StopActionException
     * @throws ClassLoadingForReflectionFailedException
     */
    public function renderCommand(string $fusionReference = null): void
    {
        $fusionReferences = $fusionReference !== null ? [$fusionReference] : array_keys($this->settings['fusionReferences']);
        $this->renderFusionReferences($fusionReferences);
    }

    /**
     * @throws StopActionException
     * @throws ClassLoadingForReflectionFailedException
     */
    protected function renderFusionReferences(array $references): void
    {
        foreach ($references as $reference) {
            $this->outputLine('Rendering Fusion Reference "%s"', [$reference]);
            $this->renderFusionReference($reference);
        }
    }

    protected function getPrototypeDefinitions(array $fusionPathPatterns)
    {
        $fusionCodeCollection = [];
        foreach ($fusionPathPatterns as $fusionPathPattern) {
            $fusionCodeCollection[] = FusionSourceCode::fromFilePath($fusionPathPattern);
        }
        $fusionConfiguration = $this->fusionParser->parseFromSource(new FusionSourceCodeCollection(...$fusionCodeCollection));
        return $fusionConfiguration->toArray()['__prototypes'] ?? [];
    }

    /**
     * @throws StopActionException
     * @throws ClassLoadingForReflectionFailedException
     */
    protected function renderFusionReference(string $reference): void
    {
        if (!isset($this->settings['fusionReferences'][$reference])) {
            $this->outputLine('Fusion Reference "%s" is not configured', [$reference]);
            $this->quit(1);
        }
        $referenceConfiguration = $this->settings['fusionReferences'][$reference];
        $prototypeDefinitions = $this->getPrototypeDefinitions($referenceConfiguration['fusionPaths']);
        $parserClassName = $referenceConfiguration['parser']['implementationClassName'];
        $parserOptions = $referenceConfiguration['parser']['options'] ?? [];

        /** @var $classParser FusionParser */
        $classParser = new $parserClassName($parserOptions);
        $prototypeReferences = [];
        foreach ($prototypeDefinitions as $prototypeName => $prototypeDefinition) {
            if($reference = $classParser->parse($prototypeDefinition, $prototypeName)) {
                $prototypeReferences[$prototypeName] = $reference;
            }
        }
        usort($prototypeReferences, static function (FusionReference $a, FusionReference $b) {
            if ($a->getTitle() === $b->getTitle()) {
                return 0;
            }

            return ($a->getTitle() < $b->getTitle()) ? -1 : 1;
        });
        $standaloneView = new StandaloneView();
        $templatePathAndFilename = $referenceConfiguration['templatePathAndFilename'] ?? $this->defaultTemplatePath;
        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assign('title', $referenceConfiguration['title'] ?? $reference);
        $standaloneView->assign('prototypeReferences', $prototypeReferences);
        file_put_contents($referenceConfiguration['savePathAndFilename'], $standaloneView->render());
        $this->outputLine('Written to: ' . $referenceConfiguration['savePathAndFilename']);
        $this->outputLine('DONE.');
    }
}