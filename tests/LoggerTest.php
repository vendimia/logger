<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vendimia\Logger\Logger;
use Vendimia\Logger\Target;

require 'vendor/autoload.php';

final class LoggerTest extends TestCase
{
    public function testCreation(): Logger
    {
        $logger = new Logger;

        $this->assertTrue($logger instanceof Logger);

        return $logger;
    }

    /** 
     * @depends testCreation
     */
    public function testErrorLog(Logger $logger): void
    {
        $target = new Target\ErrorLog;
        $logger->addTarget($target);
        $logger->warning("## WARNING ##");
    }
}
