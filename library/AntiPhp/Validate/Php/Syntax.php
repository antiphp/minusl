<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Validate\Php;

use AntiPhp\Validate\ValidateAbstract;
use AntiPhp\Validate\ValidateInterface;

/**
 * class to check the syntax of a PHP file
 *
 * @author Christian Reinecke <christian.reinecke@karlsruler.de>
 */
class Syntax extends ValidateAbstract implements ValidateInterface
{
    /**
     * @param string $filename
     * @return bool
     */
    public function isValid($filename)
    {
        $message = exec(
            sprintf(
                'php -l %s',
                escapeshellarg($filename)
            )
        );
        $isValid = strpos($message, 'No syntax errors') === 0;
        if (!$isValid) {
            $this->addError($message);
        }
        return $isValid;
    }
}