<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Validate\Json;

use AntiPhp\Validate\ValidateAbstract;
use AntiPhp\Validate\ValidateInterface;

class Syntax extends ValidateAbstract implements ValidateInterface
{
    public function isValid($filename)
    {
        $content = file_get_contents($filename);
        $json = json_decode($content);
        $isValid = $json !== null;
        if (!$isValid) {
            $jsonLastError = json_last_error();
            foreach (array('JSON_ERROR_CTRL_CHAR', 'JSON_ERROR_DEPTH', 'JSON_ERROR_STATE_MISMATCH', 'JSON_ERROR_SYNTAX', 'JSON_ERROR_UTF8', 'JSON_ERROR_NONE') as $jsonErrorConstant) {
                if (!defined($jsonErrorConstant)) {
                    continue;
                }
                if (constant($jsonErrorConstant) !== $jsonLastError) {
                    continue;
                }
                $jsonLastError = $jsonErrorConstant;
            }
            $this->addError(sprintf('Errors parsing %s: %s', $filename, $jsonLastError));
        }
        return $isValid;
    }
}