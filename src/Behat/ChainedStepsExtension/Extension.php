<?php

namespace Behat\ChainedStepsExtension;

use Behat\Behat\Extension\Extension as BaseExtension;

class Extension extends BaseExtension
{
    public function getExtensionName()
    {
        return 'chained_steps_extension';
    }
}
