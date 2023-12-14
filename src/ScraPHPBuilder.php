<?php 

declare(strict_types=1);

namespace ScraPHP;

use Monolog\Level;
use Monolog\Logger;
use ScraPHP\Writers\Writer;
use Psr\Log\LoggerInterface;
use ScraPHP\Writers\JsonWriter;
use Monolog\Handler\StreamHandler;
use ScraPHP\HttpClient\HttpClient;
use Monolog\Formatter\LineFormatter;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverHttpClient;

final class ScraPHPBuilder
{

    private ?HttpClient $httpClient = null;
    private ?LoggerInterface $logger = null;
    private ?Writer $writer = null;

    private int $retryCount = 3;
    private int $retryTime = 30;

    /**
     * Sets the HttpClient for the object and returns itself.
     *
     * @param HttpClient $httpClient The HttpClient to be set.
     * @return self The updated object.
     */
    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Sets the Logger and returns itself. If a string was passed in, it
     * will be create a Logger to this file.
     *
     * @param LoggerInterface|string $logger The logger to be set for the object.
     * @return self Returns the current object instance.
     */
    public function withLogger(LoggerInterface|string $logger): self
    {
        if( is_string($logger)){
            $this->logger = $this->createDefaultLogger($logger);
            return $this;
        }
        
        $this->logger = $logger;
        return $this;
    }

    /**
     * Sets the writer for the object.
     *
     * @param Writer $writer The writer object to be set.
     * @return self The modified object with the new writer.
     */
    public function withWriter(Writer $writer): self
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * Sets the retry count.
     *
     * @param int $retryCount The number of times the function should be retried.
     * @return self
     */
    public function withRetryCount(int $retryCount): self
    {
        $this->retryCount = $retryCount;
        return $this;
    }

    /**
     * Sets the retry time.
     *
     * @param int $retryTime The retry time in milliseconds.
     * @return self The current instance of the class.
     */
    public function withRetryTime(int $retryTime): self
    {
        $this->retryTime = $retryTime;
        return $this;
    }


    /**
     * Create a new instance of the ScraPHP class.
     *
     * @return ScraPHP
     */
    public function create(): ScraPHP
    {

        $logger = $this->logger === null 
            ? $this->createDefaultLogger('php://stdout') 
            : $this->logger;
        
        $writer = $this->writer === null 
            ? new JsonWriter('out.json') 
            : $this->writer;
            
        $writer->withLogger($logger);
        
        return new ScraPHP(
            httpClient: $this->httpClient === null ? new GuzzleHttpClient($logger) : $this->httpClient,
            logger: $logger,   
            writer: $writer,
            retryCount: $this->retryCount,
            retryTime: $this->retryTime
        );
    }


    /**
     * Initializes the logger.
     *
     * @param  string  $logfile The path to the log file.
     *
     * @throws Exception If there is an error initializing the logger.
     */
    private function createDefaultLogger(string $logfile): LoggerInterface
    {
        $logger = new Logger('SCRAPHP');
        $handler = new StreamHandler($logfile, Level::Debug);
        $formatter = new LineFormatter("%datetime% %level_name%  %message% %context% %extra%\n", 'Y-m-d H:i:s');
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}