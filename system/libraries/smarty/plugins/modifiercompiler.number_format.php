<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty number_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     number_format<br>
 * Purpose:  format a number with grouped thousands
 *
 * @author chenshuwei
 * @param array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_number_format($params, $compiler)
{
    return 'number_format(' . $params[0] . ', 2)';
}

?>