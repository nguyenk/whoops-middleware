<?php

namespace Franzl\Middleware\Whoops;

use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;
use Zend\Diactoros\Response\StringResponse;

class WhoopsRunner
{

    public static function handle($error, ServerRequestInterface $request)
    {
        $method = Run::EXCEPTION_HANDLER;
        //load Whoops depending on the accept format
        $whoops = self::getWhoopsInstance($request);
        ob_start();
        $whoops->$method($error);
        $response = ob_get_clean();

        return StringResponse::html($response, 500);
    }

    /**
     * Returns the whoops instance or create one.
     *
     * @param ServerRequestInterface $request
     *
     * @return Run
     */
    private static function getWhoopsInstance(ServerRequestInterface $request)
    {
        $whoops = new Run();
        if (php_sapi_name() === 'cli') {
            $whoops->pushHandler(new PlainTextHandler());
            return $whoops;
        }
        $format = FormatNegotiator::getFormat($request);
        switch ($format){
            case "json":
                $handler = new JsonResponseHandler();
                $handler->addTraceToOutput(true);
                break;
            case "html":
                $handler = new PrettyPageHandler();
                break;
            case "txt":
                $handler = new PlainTextHandler();
                $handler->addTraceToOutput(true);
                break;
            case "xml":
                $handler = new XmlResponseHandler();
                $handler->addTraceToOutput(true);
                break;
            default:
                if (empty($format)){
                    $handler = new PrettyPageHandler();
                }else{
                    $handler = new PlainTextHandler();
                    $handler->addTraceToOutput(true);
                }
        }

        $whoops->pushHandler($handler);
        return $whoops;
    }
}
