<?php
namespace FoamyCastle\ErrorStack;
interface ErrorEventInterface
{
    function onRaise($context=null):void;
    function onThrow($context=null):void;
    function setContext(mixed $context):void;
}