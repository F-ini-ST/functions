<?php

/**
 * Table-view var_dump's version
 * @param mixed $var variable to dump
 * @param array $args <i>[optional]</i> options array
 */
function pretty_var_dump($var, $args = array()) {
    $defautls = array(
        'before_table_text' => '',
        'min_width' => 250,
        'skip_int_indices' => false,
        'dump_values' => true,
        'depth' => 0,
        'max-depth' => 10,
        'echo' => true,
        'show_classes_names' => true,
        'cut_keys' => false,
    );
    if (!is_array($args)) {
        $args = array();
    }
    $args = array_merge($defautls, $args);
    $args['depth'] = !empty($args['depth']) ? $args['depth'] + 1 : 1;
    $echo = $args['echo'];
    $args['echo'] = true;
    $classname_str = "";
    if (is_object($var)):
        if ($args['show_classes_names']):
            $classname_str = "<u><i>object</i>(<b>" . get_class($var) . "</b>)</u>\n";
        endif;
        $var = (array) $var;
        $color = '#00CC00';
    else: $color = '#333';
    endif;
    if ($echo === false) {
        ob_start();
    }
    if (!defined("PRETTY_VAR_DUMP_STYLES")) {
        define("PRETTY_VAR_DUMP_STYLES", 1);
        echo "<style>\n    pre { white-space: pre-line; padding:0; margin: 0;}\n" .
        "    .pretty_var_dump__table td { padding:1px;}\n" .
        "    .pretty_var_dump__table { min-width:{$args['min_width']}px;}\n" .
        "</style>\n";
    }
    if ($args['depth']==1) { echo $args['before_table_text'];}
    if (is_array($var)):
        echo str_pad("", 6 * ($args['depth'] - 1), " ") . "$classname_str<table class=\"pretty_var_dump__table\" style='border:2px solid $color; min-width:{$args['min_width']}px;'>\n" .
        str_pad("", 6 * ($args['depth'] - 1) + 2, " ") . "<tbody>\n";
        foreach ($var as $k => $v):
            if (!is_int($k) || !$args['skip_int_indices']):
                if ($args['cut_keys'])
                    $k = (strlen($k) > $args['cut_keys'] + 2) ? "<span title='$k'>" . substr($k, 0, $args['cut_keys']) . "..</span>" : $k;
                echo str_pad("", 6 * ($args['depth'] - 1) + 2, " ") . "<tr class='depth-{$args['depth']}'>\n" .
                str_pad("", 6 * ($args['depth'] - 1) + 4, " ") . "<td style='border-right: 2px #4444FF dotted; text-align: right'>\n" .
                str_pad("", 6 * ($args['depth'] - 1) + 6, " ") . "[" . (is_int($k) ? "(int)" : "") . $k . "]=>\n" . //(is_object($v) ? "<br /><u><i>object</i>(<b>".get_class($v)."</b>)</u>\n" : "") .
                str_pad("", 6 * ($args['depth'] - 1) + 4, " ") . "</td>\n" .
                str_pad("", 6 * ($args['depth'] - 1) + 4, " ") . "<td>\n";
                if ($args['depth'] < $args['max-depth']):
                    pretty_var_dump($v, $args);
                else: echo "<pre><b>Max depth reached!</b></pre>";
                endif;
                echo str_pad("", 6 * ($args['depth'] - 1) + 4, " ") . "</td>\n" .
                str_pad("", 6 * ($args['depth'] - 1) + 2, " ") . "</tr><!--depth-{$args['depth']}-->\n";
            endif;
        endforeach;
        echo str_pad("", 6 * ($args['depth'] - 1) + 2, " ") . "</tbody>\n" .
        str_pad("", 6 * ($args['depth'] - 1), " ") . "</table>\n";
    else:
        if ($args['dump_values']):
            echo str_pad("", 6 * ($args['depth'] - 1), " ") . "<pre>";
            var_dump($var);
            echo str_pad("", 6 * ($args['depth'] - 1), " ") . "</pre>\n";
        else: echo str_pad("", 6 * ($args['depth'] - 1), " ") . $var . "\n";
        endif;
    endif;
    if ($echo === false) {
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}