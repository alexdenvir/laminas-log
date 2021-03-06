<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Filter\Mock as MockFilter;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Psr as PsrWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @coversDefaultClass \Laminas\Log\Writer\Psr
 * @covers ::<!public>
 */
class PsrTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructWithPsrLogger()
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $writer    = new PsrWriter($psrLogger);
        $this->assertAttributeSame($psrLogger, 'logger', $writer);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithOptions()
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $formatter = new SimpleFormatter();
        $filter    = new MockFilter();
        $writer = new PsrWriter([
            'filters'   => $filter,
            'formatter' => $formatter,
            'logger'    => $psrLogger,
        ]);

        $this->assertAttributeSame($psrLogger, 'logger', $writer);
        $this->assertAttributeSame($formatter, 'formatter', $writer);

        $filters = self::readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    /**
     * @covers ::__construct
     */
    public function testFallbackLoggerIsNullLogger()
    {
        $writer = new PsrWriter;
        $this->assertAttributeInstanceOf(NullLogger::class, 'logger', $writer);
    }

    /**
     * @dataProvider priorityToLogLevelProvider
     */
    public function testWriteLogMapsLevelsProperly($priority, $logLevel)
    {
        $message = 'foo';
        $extra   = ['bar' => 'baz'];

        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($logLevel),
                $this->equalTo($message),
                $this->equalTo($extra)
            );

        $writer = new PsrWriter($psrLogger);
        $logger = new Logger();
        $logger->addWriter($writer);

        $logger->log($priority, $message, $extra);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function priorityToLogLevelProvider()
    {
        return [
            'emergency' => [Logger::EMERG, LogLevel::EMERGENCY],
            'alert'     => [Logger::ALERT, LogLevel::ALERT],
            'critical'  => [Logger::CRIT, LogLevel::CRITICAL],
            'error'     => [Logger::ERR, LogLevel::ERROR],
            'warn'      => [Logger::WARN, LogLevel::WARNING],
            'notice'    => [Logger::NOTICE, LogLevel::NOTICE],
            'info'      => [Logger::INFO, LogLevel::INFO],
            'debug'     => [Logger::DEBUG, LogLevel::DEBUG],
        ];
    }
}
