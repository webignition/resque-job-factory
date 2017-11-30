<?php

namespace webignition\Tests\ResqueJobFactory\Job;

use ResqueBundle\Resque\Job;

class AbstractJob extends Job
{
    /**
     * {@inheritdoc}
     */
    public function run($args)
    {
        return null;
    }
}
