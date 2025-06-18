<?php
namespace FoamyCastle\ErrorStack;
interface ErrorEventInterface
{
    function onRaise($context=null): self;
    function onThrow($context=null): self;
    function setContext(mixed &$context): self;
    function suppressThrow(bool $suppress):self;
}