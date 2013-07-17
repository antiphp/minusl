<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Validate;

abstract class ValidateAbstract
{
    protected $errors = array();

    public function hasError()
    {
        return count($this->errors) > 0;
    }

    public function getError()
    {
        $error = implode(PHP_EOL, $this->errors);
        return $error;
    }

    protected function addError($message)
    {
        $this->errors[] = trim($message);
    }
}