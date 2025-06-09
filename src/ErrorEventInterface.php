<?php
namespace FoamyCastle\ErrorStack;
interface ErrorEventInterface
{
    function onRaise($context=null):void;
    function onThrow($context=null):\Throwable;
}