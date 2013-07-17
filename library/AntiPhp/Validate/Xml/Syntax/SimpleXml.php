<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Validate\Xml\Syntax;

use AntiPhp\Validate\ValidateAbstract;
use AntiPhp\Validate\ValidateInterface;

class SimpleXml extends ValidateAbstract implements ValidateInterface
{
    public function isValid($filename)
    {
        $simpleXml = @simplexml_load_file($filename); // FIXME
        $isValid = $simpleXml instanceof \SimpleXMLElement;
        if (!$isValid) {
            $error = libxml_get_last_error();
            $this->addError(sprintf('Errors parsing %s: %s', $filename, $error->message));
        }
        return $isValid;
    }
}