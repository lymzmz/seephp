<?php

class see_view_input {

    static public function resolver( $type_str, $params_arr )
    {
        $method_name = '_parse_' . $type_str;
        if ( !method_exists('see_view_input', $method_name) )
            throw new Exception('unkown type \''.$type_str.'\' for function \'input\'');

        return self::$method_name( $params_arr );
    }

    static private function _parse_select( $params_arr )
    {
        $html = '<?php foreach ( '.$params_arr['options'].' as $key=>$val ) {
            echo \'<option value="\'.$key.\'">\'.$val.\'</option>\';
            } ?>';
        $html .= '</select>';
        $select = '<select name="<?php echo '.$params_arr['name'].'; ?>"';
        unset($params_arr['options'], $params_arr['name']);
        foreach ( $params_arr as $key => $val) {
            $select .= ' '.$key.'="<?php echo '.$val.'; ?>"';
        }
        $html = $select.'>'.$html;

        return $html;
    }

    static private function _parse_radio( $params_arr )
    {
        $html = '<?php $name='.$params_arr['name'].';foreach ( '.$params_arr['options'].' as $key => $val ) {
            $checked=$key=='.$params_arr['checked'].'?\'checked\':\'\';
            echo \'<input type="radio" name="\'.$name.\'" \'.$checked.\' value="\'.$key.\'"/> \'.$val;
            } ?>';

        return $html;
    }

    static private function _parse_checkbox( $params_arr )
    {
        $html = '<?php $name='.$params_arr['name'].';foreach ( '.$params_arr['options'].' as $key => $val ) {
            $checked=$key=='.$params_arr['checked'].'?\'checked\':\'\';
            echo \'<input type="checkbox" name="\'.$name.\'" \'.$checked.\' value="\'.$key.\'"/> \'.$val;
            } ?>';

        return $html;
    }

    static private function _parse_text( $params_arr )
    {
        $html = '<input type="text" name="<?php echo '.$params_arr['name'].'; ?>" value="<?php echo '.$params_arr['value'].'; ?>"';
        unset($params_arr['name'], $params_arr['value']);
        foreach ( $params_arr as $key => $val) {
            $html .= ' '.$key.'="<?php echo '.$val.'; ?>"';
        }
        $html .= '/>';

        return $html;
    }

    static private function _parse_textarea( $params_arr )
    {
        $html = '<?php echo '.$params_arr['value'].'; ?></textarea>';
        $textarea = '<textarea name="<?php echo '.$params_arr['name'].'; ?>"';
        unset($params_arr['name'], $params_arr['value']);
        foreach ( $params_arr as $key => $val) {
            $textarea .= ' '.$key.'="<?php echo '.$val.'; ?>"';
        }
        $html = $textarea .'>'. $html;

        return $html;
    }

    static private function _parse_object( $params_arr )
    {

    }

}
