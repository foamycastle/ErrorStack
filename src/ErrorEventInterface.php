<?php
namespace FoamyCastle\ErrorStack;
interface ErrorEventInterface
{
    function onRaise():void;
    function onThrow():void;
}