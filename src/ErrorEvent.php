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

    public static function AutoRaise(bool $auto): static
    {
        self::$autoRaise=$auto;
    }

    public static function AutoThrow(ErrorEvent $errorEvent): ErrorEventInterface
    {

    }

    /**
     * Turns on auto handling so that ErrorEvent::ActivateHandler does not need to be invoked
     * before a throw
     * @param bool $auto
     * @return ErrorEvent
     */
    public static function AutoHandle(bool $auto=true): static
    {
        if($auto){
            if(!self::$handlerActive){
                self::ActivateHandler();
            }
        }
    }

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
     * @return ErrorEvent
     */
    public static function Register(ErrorEvent $errorEvent):ErrorEvent
    {
        $name=$errorEvent->name;
        self::$errorThrowables[$name] = $errorEvent;
        return $errorEvent;
    }

    public static function Handler(\Throwable $exception):void
    {
        if(!($exception instanceof ErrorEvent)){
            if(!empty(self::$oldExceptionHandler)){
                (self::$oldExceptionHandler)($exception);
            }
        }
        if($exception instanceof ErrorEventInterface) {
            if(self::$autoRaise){
                $exception->onRaise();
            }
            if(self::$autoThrow){
                $exception->onThrow();
                return;
            }
            if(!$exception->suppressThrow) {
                set_exception_handler(null);
                throw $exception;
            }
        }
    }

    public static function ActivateHandler():void
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

        //argument provided is not registered
        if(!self::isRegistered($name)){
            return null;
        }

        //
        switch($name){
            case 'Raise':
                self::$errorThrowables[$arguments[0]]->onRaise($arguments[1] ??  null);
                break;
            case 'Throw':
                self::$errorThrowables[$arguments[0]]->onThrow($arguments[1] ??  null);
                break;
            default:
                if(!empty($arguments)){
                    return call_user_func(self::$errorThrowables[$name], $arguments[0] ??  null);
                }
                return self::$errorThrowables[$name] ?? null;
        }
    }

    protected string $name;
    protected mixed $context;
    protected bool $autoThrow=false;
    protected bool $autoRaise=false;
    protected bool $suppressThrow=false;
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

    function suppressThrow(bool $suppress): static
    {
        $this->suppressThrow=$suppress;
        return $this;
    }

    public function setContext(mixed &$context): static
    {
        $this->context=$context;
        return $this;
    }
    public function __invoke(...$args):static
    {
        if(is_int($args[0])){
            switch($args[0]){
                case self::SET_CODE:
                    if(!is_int($args[1])){
                        return $this;
                    }
                    $this->code=$args[1];
                    break;
                case self::SET_MESSAGE:
                    if(!is_string($args[1])){
                        return $this;
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