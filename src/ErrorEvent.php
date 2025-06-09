<?php
namespace FoamyCastle\ErrorStack;
abstract class ErrorEvent implements ErrorEventInterface
{
    /**
     * A stack of error events
     * @var array<string,ErrorEvent>
     */
    private static array $errorThrowables = [];

    /**
     * Register an ErrorEvent object
     * @param ErrorEvent $errorEvent
     * @param string $name
     * @return void
     */
    public static function Register(ErrorEvent $errorEvent, string $name=''):void
    {
        if(empty($name)){
            $name=$errorEvent->name;
        }
        self::$errorThrowables[$name] = $errorEvent;
    }

    /**
     * Call the onRaise method on the ErrorEvent object
     * @param string $name
     * @return void
     */
    public static function Raise(string $name):void
    {
        self::isRegistered($name) && self::$errorThrowables[$name]->onRaise();
    }

    /**
     * Call the onThrow method on the ErrorEvent object
     * @param string $name
     * @return void
     */
    public static function Throw(string $name):void
    {
        self::isRegistered($name) && self::$errorThrowables[$name]->onThrow();
    }
    private static function isRegistered(string $name):bool
    {
        return isset(self::$errorThrowables[$name]);
    }
    public readonly \Throwable $throwable;
    public readonly string $name;
    public function __construct(string $name, string $throwable)
    {
        if(!class_exists($throwable) || (!($throwable instanceof \Throwable)) ){
            throw new \Exception("$throwable is not a valid Throwable");
        }
        self::Register($this, $throwable);
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
                self::$errorThrowables[$arguments[0]]->onThrow();
                return;
            default:
                throw new \Exception("Unknown ErrorEvent::$arguments[0]");
        }
    }

}