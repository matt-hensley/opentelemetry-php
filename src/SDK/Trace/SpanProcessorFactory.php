<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

class SpanProcessorFactory
{
    use EnvironmentVariablesTrait;

    public function fromEnvironment(?SpanExporterInterface $exporter = null): SpanProcessorInterface
    {
        if ($exporter === null) {
            return new NoopSpanProcessor();
        }

        $name = $this->getEnumFromEnvironment(Env::OTEL_PHP_TRACES_PROCESSOR);
        switch ($name) {
            case Values::VALUE_BATCH:
                return new BatchSpanProcessor(
                    $exporter,
                    ClockFactory::getDefault(),
                    $this->getIntFromEnvironment(Env::OTEL_BSP_MAX_QUEUE_SIZE, BatchSpanProcessor::DEFAULT_MAX_QUEUE_SIZE),
                    $this->getIntFromEnvironment(Env::OTEL_BSP_SCHEDULE_DELAY, BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY),
                    $this->getIntFromEnvironment(Env::OTEL_BSP_EXPORT_TIMEOUT, BatchSpanProcessor::DEFAULT_EXPORT_TIMEOUT),
                    $this->getIntFromEnvironment(Env::OTEL_BSP_MAX_EXPORT_BATCH_SIZE, BatchSpanProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE),
                );
            case Values::VALUE_SIMPLE:
                return new SimpleSpanProcessor($exporter);
            case Values::VALUE_NOOP:
            case Values::VALUE_NONE:
                return NoopSpanProcessor::getInstance();
            default:
                throw new InvalidArgumentException('Unknown processor: ' . $name);
        }
    }
}
