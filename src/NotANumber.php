<?php

namespace FoamyCastle\ErrorStack;

use FoamyCastle\ErrorStack\ErrorEvent;

class NotANumber extends ErrorEvent
{
    public const string NAME='NotANumber';
    public function __construct() {
        parent::__construct(
            self::NAME,
            "%s is not a number"
        );
    }

    function onRaise($context = null): void
    {
        echo sprintf($this->message, $context);
    }

    function getThrowable($context = null): \Throwable|null
    {
        if(is_int($context)) return null;
        return new self();
    }

    function onThrow($context = null): void
    {
        echo sprintf($this->message, $context ?? $this->context);
    }

}