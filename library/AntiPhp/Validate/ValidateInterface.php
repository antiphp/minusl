<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-16 22:10:54 +0200 (Di, 16 Jul 2013) $
 * @since $Rev: 1 $
 */
namespace AntiPhp\Validate;

interface ValidateInterface
{
    /**
     * @param string $filename
     */
    public function isValid($filename);
}