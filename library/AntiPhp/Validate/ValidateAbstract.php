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

/**
 * abstract class to provide error storage
 *
 * @author Christian Reinecke <christian.reinecke@karlsruler.de>
 */
abstract class ValidateAbstract
{
    /**
     * @var string[]
     */
    protected $errors = array();

    /**
     * @return boolean
     */
    public function hasError()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return string
     */
    public function getError()
    {
        $error = implode(PHP_EOL, $this->errors);
        return $error;
    }

    /**
     * @param string $message
     */
    protected function addError($message)
    {
        $this->errors[] = trim($message);
    }
}