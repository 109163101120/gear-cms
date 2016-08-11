<?php

class formRadio extends formField {

    var $output = [];
    var $inline = false;

    public function __construct($name, $value, $attributes = []) {

        if(in_array('inline', $attributes)) {
            $this->inline = true;
            unset($attributes['inline']);
        }

        parent::__construct($name, $value, $attributes);

    }

    public function add($name, $value, $attributes = []) {

        $attributes['type'] = 'radio';
        $attributes['value'] = $name;
        $attributes['name'] = $this->name;

        if($attributes['value'] ==  $this->value) {
            $attributes['checked'] = 'checked';
        }

        $this->output[$attributes['value']] = ['value'=>$value, 'attr'=>$attributes];

        return $this;

    }

    public function del($name) {
        unset($this->output[$name]);
        return $this;
    }

    public function get() {

        $return = '';

        foreach($this->output as $val) {
            $id = preg_replace('/[^a-zA-Z0-9]+/', '-', $val['value']);
            $inline = ($this->inline) ? '-inline' : '';
            $return .= '
                <div class="radio'.$inline.'">
                    <input id="'.$id.'Radio" '.$this->convertAttr($val['attr']).'>
                    <label for="'.$id.'Radio">'.$val['value'].'</label>
                </div>
            ';
        }

        return $return;

    }

}

?>
