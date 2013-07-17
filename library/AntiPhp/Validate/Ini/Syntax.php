<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Validate\Ini;

use AntiPhp\Validate\ValidateAbstract;
use AntiPhp\Validate\ValidateInterface;

/**
 * class to check the syntax of the provided INI file
 *
 * @author Christian Reinecke <christian.reinecke@karlsruler.de>
 */
class Syntax extends ValidateAbstract implements ValidateInterface
{
    /**
     * (non-PHPdoc)
     * @see \AntiPhp\Validate\ValidateInterface::isValid()
     */
    public function isValid($filename)
    {
        $ini = @parse_ini_file($filename); // FIXME register error handler instead
        $isValid = $ini !== false;
        if (!$isValid) {
            $this->addError(sprintf('Errors parsing %s: parse_ini_file() failed', $filename));
        }
        return $isValid;
    }
}