<?php

namespace Behat\ChainedStepsExtension\Step;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Node\StepNode;

/**
 * `Then` substep.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Then extends SubStep
{
    /**
     * Initializes `Then` sub-step.
     */
    public function __construct()
    {
        $arguments = func_get_args();
        $text = array_shift($arguments);

        parent::__construct('Then', $text, $arguments);
    }
}
