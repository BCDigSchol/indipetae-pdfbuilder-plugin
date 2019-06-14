<?php

class IndipetaePDFBuilder_Logger
{
    public function info(string $message)
    {
        $this->log($message, Zend_Log::INFO);
    }

    public function debug(string $message)
    {
        $this->log($message, Zend_Log::DEBUG);
    }

    public function error(string $message)
    {
        $this->log($message, Zend_Log::ERR);
    }

    private function log(string $message, $level)
    {
        _log($message, $level);
    }
}