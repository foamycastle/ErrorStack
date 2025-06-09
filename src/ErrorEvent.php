<?php
namespace FoamyCastle\ErrorStack;
use Throwable;

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
     * @return void
     */
    public static function Register(ErrorEvent $errorEvent):void
    {
        $name=$errorEvent->name;
        //if the name property is blank, let the object be registered by its class name
        if(empty($name)){
            $lastSeparator=strripos($errorEvent->throwable::class,'\\');
            if($lastSeparator===false){
                $name=$errorEvent->throwable::class;
            }
            $name=substr($name,$lastSeparator);
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

    /**
     * Determine if a name is already registered in the ErrorStack
     * @param string $name
     * @return bool
     */
    private static function isRegistered(string $name):bool
    {
        return isset(self::$errorThrowables[$name]);
    }

    /**
     * The throwable object to be triggered
     * @var Throwable|mixed
     */
    public readonly \Throwable $throwable;
    public function __construct(
        string $errorClass,
        public readonly string $name="",
        public readonly string $message="",
        public readonly int $code=0,
        public readonly ?\Throwable $previous=null
    )
    {
        //error class does not exist
        if(!(class_exists($errorClass))){
            throw new \Error("Class $errorClass does not exist");
        }

        //test for valid error object
        if(!($errorClass instanceof Throwable)){
            throw new \Error("Class $errorClass does not implement Throwable");
        }

        //create a new error object and registered it
        $this->throwable=new $errorClass($this->message,$this->code,$name,$previous);
        self::Register($this);
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