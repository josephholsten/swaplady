<?php
require_once 'Zend/Log.php';

class Swaplady_Log extends Zend_Log
{
    public function entering()
    {
        $trace = debug_backtrace();
        // shift the current frame off the trace
        // we're looking for the frame entering was called in
        array_shift($trace);
        $frame = array_shift($trace);
        $class = isset($frame['class']) ? $frame['class'] : '';
        $type = isset($frame['type']) ? $frame['type'] : '';
        $function = isset($frame['function']) ? $frame['function'] : '';
        $this->debug("Entering {$class}{$type}{$function}()");
    }

    public function exiting()
    {
        $trace = debug_backtrace();
        // shift the current frame off the trace
        // we're looking for the frame entering was called in
        array_shift($trace);
        $frame = array_shift($trace);
        $class = isset($frame['class']) ? $frame['class'] : '';
        $type = isset($frame['type']) ? $frame['type'] : '';
        $function = isset($frame['function']) ? $frame['function'] : '';
        $this->debug("Exiting {$class}{$type}{$function}()");
    }
    
    public function debug($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::DEBUG, $logName_or_fields, $logName);
    }

    public function info($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::INFO, $logName_or_fields, $logName);
    }
    
    public function notice($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::NOTICE, $logName_or_fields, $logName);
    }
        
    public function err($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::ERR, $logName_or_fields, $logName);
    }

    public function warning($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::WARN, $logName_or_fields, $logName);
    }
    
    public function critical($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::CRIT, $logName_or_fields, $logName);
    }
    
    public function error($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::ALERT, $logName_or_fields, $logName);
    }
    
    public function emergency($message, $logName_or_fields = null, $logName = null)
    {
        $this->log($message, Zend_Log::EMERG, $logName_or_fields, $logName);
    }
}