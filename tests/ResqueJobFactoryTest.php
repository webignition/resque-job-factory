<?php

namespace webignition\Tests\ResqueJobFactory;

use webignition\ResqueJobFactory\ResqueJobFactory;
use webignition\Tests\ResqueJobFactory\Job\QueueOneJob;
use webignition\Tests\ResqueJobFactory\Job\QueueTwoJob;

class ResqueJobFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $queues = [
        'queue-one' => [
            ResqueJobFactory::KEY_JOB_CLASS_NAME => QueueOneJob::class,
        ],
        'queue-two' => [
            ResqueJobFactory::KEY_JOB_CLASS_NAME => QueueTwoJob::class,
            ResqueJobFactory::KEY_REQUIRED_ARGS => [
                'limit',
                'offset',
            ],
            ResqueJobFactory::KEY_PARAMETERS => [
                'foo' => 'bar',
            ],
        ],
    ];

    /**
     * @var ResqueJobFactory
     */
    private $resqueJobFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->resqueJobFactory = new ResqueJobFactory($this->queues);
    }

    public function testCreateInvalidQueue()
    {
        $queue = 'foo';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Queue "foo" is not valid');
        $this->expectExceptionCode(ResqueJobFactory::EXCEPTION_CODE_INVALID_QUEUE);

        $this->resqueJobFactory->create($queue);
    }

    /**
     * @dataProvider createMissingRequiredArgumentsDataProvider
     *
     * @param string $queue
     * @param array $args
     * @param string $expectedExceptionMessage
     */
    public function testCreateMissingRequiredArguments($queue, $args, $expectedExceptionMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectExceptionCode(ResqueJobFactory::EXCEPTION_CODE_MISSING_REQUIRED_ARG);

        $this->resqueJobFactory->create($queue, $args);
    }

    /**
     * @return array
     */
    public function createMissingRequiredArgumentsDataProvider()
    {
        return [
            'queue-two missing limit' => [
                'queue' => 'queue-two',
                'args' => [],
                'expectedExceptionMessage' => 'Required argument "limit" is missing',
            ],
            'queue-two offset limit' => [
                'queue' => 'queue-two',
                'args' => [
                    'limit' => 1,
                ],
                'expectedExceptionMessage' => 'Required argument "offset" is missing',
            ],
        ];
    }

    /**
     * @dataProvider createSuccessDataProvider
     *
     * @param string $queue
     * @param array $args
     * @param string $expectedJobClassName
     * @param string $expectedQueue
     * @param array $expectedArgs
     */
    public function testCreateSuccess($queue, $args, $expectedJobClassName, $expectedQueue, $expectedArgs)
    {
        $job = $this->resqueJobFactory->create($queue, $args);

        $this->assertInstanceOf($expectedJobClassName, $job);
        $this->assertEquals($expectedQueue, $job->queue);
        $this->assertEquals($expectedArgs, $job->args);
    }

    /**
     * @return array
     */
    public function createSuccessDataProvider()
    {
        return [
            'queue-one' => [
                'queue' => 'queue-one',
                'args' => [],
                'expectedJobClassName' => QueueOneJob::class,
                'expectedQueue' => 'queue-one',
                'expectedArgs' => [],
            ],
            'queue-two' => [
                'queue' => 'queue-two',
                'args' => [
                    'limit' => 10,
                    'offset' => 3,
                ],
                'expectedJobClassName' => QueueTwoJob::class,
                'expectedQueue' => 'queue-two',
                'expectedArgs' => [
                    'limit' => 10,
                    'offset' => 3,
                    'parameters' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getJobClassNameDataProvider
     *
     * @param string $queue
     * @param string $expectedJobClassName
     */
    public function testGetJobClassName($queue, $expectedJobClassName)
    {
        $jobClassName = $this->resqueJobFactory->getJobClassName($queue);

        $this->assertEquals($expectedJobClassName, $jobClassName);
    }

    /**
     * @return array
     */
    public function getJobClassNameDataProvider()
    {
        return [
            'none' => [
                'queue' => 'foo',
                'expectedJobClassName' => null,
            ],
            'queue-one' => [
                'queue' => 'queue-one',
                'expectedJobClassName' => QueueOneJob::class,
            ],
            'queue-two' => [
                'queue' => 'queue-two',
                'expectedJobClassName' => QueueTwoJob::class,
            ],
        ];
    }
}
