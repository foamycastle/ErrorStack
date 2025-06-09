<?php
namespace FoamyCastle\ErrorStack;
interface ErrorEventInterface
{
    function onRaise($context=null):void;
    function onThrow($context=null):void;
    function getThrowable($context=null):\Throwable|null;
    function setContext(mixed $context):void;
}