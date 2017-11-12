<?php

namespace VMBundle\Exception;

class VMLogicException extends \LogicException
{
    /**
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null)
    {
        parent::__construct($message, 406);
    }
}