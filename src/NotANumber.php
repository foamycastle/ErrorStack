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

    function onRaise($context = null): NotANumber
    {
        echo sprintf($this->message, $context ?? $this->context);
        return $this;
    }

    function onThrow($context = null): NotANumber
    {
        echo sprintf($this->message, $context ?? $this->context);
        return $this;
    }

}