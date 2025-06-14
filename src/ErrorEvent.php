<?php
namespace FoamyCastle\ErrorStack;
use Throwable;

abstract class ErrorEvent extends \Exception implements ErrorEventInterface
{
    public const int SET_CONTEXT=64;
    public const int SET_MESSAGE=128;
    public const int SET_CODE=256;

    /**
     * A stack of error events
     * @var array<string,ErrorEvent>
     */
    private static array $errorThrowables = [];

    /**
     * Stores the previous exception handler
     * @var callable $oldExceptionHandler
     */
    private static $oldExceptionHandler=null;

    /**
     * Indicates whether the exception handler is active
     * @var bool
     */
    protected static bool $handlerActive=false;

    /**
     * Public getter for $handlerActive field
     * @return bool
     */
    public static function isHandlerActive(): bool
    {
        return self::$handlerActive;
    }

    /**
     * Register an ErrorEvent object
     * @param ErrorEvent $errorEvent
     * @return void
     */
    public static function Register(ErrorEvent $errorEvent):void
    {
        $name=$errorEvent->name;
        self::$errorThrowables[$name] = $errorEvent;
    }

    public static function Handler(\Throwable $exception):void
    {
        $implements=class_implements($exception);
        if(!($exception instanceof ErrorEvent)){
            if(!empty(self::$oldExceptionHandler)){
                (self::$oldExceptionHandler)($exception);
            }
        }
        if(in_array(ErrorEvent::class, $implements)) {
            $exception->onThrow();
        }
    }

    public static function ActivateHandler()
    {
        self::$oldExceptionHandler=set_exception_handler([ErrorEvent::class,'Handler']);
    }

    public static function DeactivateHandler():void
    {
        if(!empty(self::$oldExceptionHandler)){
            set_exception_handler(self::$oldExceptionHandler);
        }else {
            set_exception_handler(null);
        }
    }

    /**
     * Call the onRaise method on the ErrorEvent object
     * @param string $name
     * @return void
     */
    public static function Raise(string $name, $context=null):void
    {
        self::isRegistered($name) && self::$errorThrowables[$name]->onRaise($context);
    }

    /**
     * Determine if a name is already registered in the ErrorStack
     * @param string $name
     * @return bool
     */
    private static function isRegistered(string $name):bool
    {
        return isset(self::$errorThrowables[$name]);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        //no argument provided or argument is not a string
        if(empty($arguments[0]) || !is_string($arguments[0])){
            throw new \Exception("argument provided to ErrorEvent::$name is not a string");
        }

        //argument provided is not registered
        if(!self::isRegistered($arguments[0])){
            return;
        }

        //
        switch($name){
            case 'Raise':
                self::$errorThrowables[$arguments[0]]->onRaise();
                return;
            case 'Throw':
                self::$errorThrowables[$arguments[0]]->getThrowable();
                return;
            default:
                throw new \Exception("Unknown ErrorEvent::$arguments[0]");
        }
    }

    protected string $name;
    protected mixed $context;
    public function __construct(
        string $name,
        string $message='',
        int $code=0,
        \Throwable $previous = null
    )
    {
        $this->name=$name;
        parent::__construct($message, $code, $previous);
        self::Register($this);
    }
    public function setContext(mixed $context): void
    {
        $this->context=$context;
    }
    public function __invoke(...$args)
    {
        if(is_int($args[0])){
            switch($args[0]){
                case self::SET_CODE:
                    if(!is_int($args[1])){
                        return null;
                    }
                    $this->code=$args[1];
                    break;
                case self::SET_MESSAGE:
                    if(!is_string($args[1])){
                        return null;
                    }
                    $this->message=$args[1];
                    break;
                case self::SET_CONTEXT:
                    $this->setContext($args[1]);
                    break;
            }
        }
        return $this;
    }

}