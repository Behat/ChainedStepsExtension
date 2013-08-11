<?php

namespace Behat\ChainedStepsExtension\Definition\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\Event\DefinitionCarrierEvent;
use Behat\Behat\Definition\Event\ExecuteDefinitionEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Exception\UndefinedException;
use Behat\Behat\Suite\SuiteInterface;
use Behat\ChainedStepsExtension\Step\SubStep;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Execute chained steps.
 * Listens to EXECUTE_DEFINITION after definition is executed and executes substeps from returns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExecuteChainedSteps extends DispatchingService implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::EXECUTE_DEFINITION => array('executeChainedSteps', -50));
    }

    /**
     * Executes chained steps from definition return.
     *
     * @param ExecuteDefinitionEvent $event
     */
    public function executeChainedSteps(ExecuteDefinitionEvent $event)
    {
        if (!$event->isExecuted() || null === $event->getReturn()) {
            return;
        }

        $returnObject = is_array($event->getReturn()) ? $event->getReturn() : array($event->getReturn());
        foreach ($returnObject as $object) {
            if (!is_object($object)) {
                continue;
            }
            if (!$object instanceof SubStep) {
                continue;
            }

            $object->setLanguage($event->getStep()->getLanguage());
            $execution = $this->getExecutionEvent($event->getSuite(), $object, $event->getContextPool());
            $this->dispatch(EventInterface::EXECUTE_DEFINITION, $execution);
        }
    }

    /**
     * Returns execution event.
     *
     * @param SuiteInterface       $suite
     * @param StepNode             $step
     * @param ContextPoolInterface $contexts
     *
     * @throws UndefinedException
     *
     * @return ExecuteCalleeEvent
     */
    protected function getExecutionEvent(
        SuiteInterface $suite,
        StepNode $step,
        ContextPoolInterface $contexts
    )
    {
        $definitionProvider = new DefinitionCarrierEvent($suite, $contexts, $step);
        $this->dispatch(EventInterface::FIND_DEFINITION, $definitionProvider);

        if (!$definitionProvider->hasDefinition()) {
            throw new UndefinedException($step->getText());
        }

        $definition = $definitionProvider->getDefinition();
        $arguments = $definitionProvider->getArguments();

        return new ExecuteDefinitionEvent($suite, $contexts, $step, $definition, $arguments);
    }
}
