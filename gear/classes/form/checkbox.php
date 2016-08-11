<?php

class formCheckbox extends formField {

    var $output = [];
    var $inline = false;
    var $divSwitch = false;

    public function __construct($name, $value, $attributes = []) {

        if(in_array('switch', $attributes)) {
            $this->divSwitch = true;
            unset($attributes['switch']);
        }

        if(in_array('inline', $attributes)) {
            $this->inline = true;
            unset($attributes['inline']);
        }

        parent::__construct($name, $value, $attributes);
        $this->setChecked();

    }

    public function setChecked() {

        if(!is_array($this->value)) {
            $this->value = explode('|', $this->value);
        }

        $this->value = array_flip($this->value);

        return $this;

    }

    public function add($name, $value, $attributes = []) {

        $attributes['type'] = 'checkbox';
        $attributes['value'] = $name;
        $attributes['name'] = $this->name;

        if(isset($this->value[$attributes['value']])) {
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

        $count = count($this->output);
        foreach($this->output as $val) {
            if($count > 1) {
                $val['attr']['name'] = $this->name.'[]';
            }
            $id = preg_replace('/[^a-zA-Z0-9]+/', '-', $val['value']);
            $inline = ($this->inline) ? '-inline' : '';
            if($this->divSwitch) {
                $return .= '
                    <div class="switch'.$inline.'">
                        <input id="'.$id.'Checkbox" '.$this->convertAttr($val['attr']).'>
                        <label for="'.$id.'Checkbox"></label>
                        <div>'.$val['value'].'</div>
                    </div>
                ';
            } else {
                $return .= '
                    <div class="checkbox'.$inline.'">
                        <input id="'.$id.'Checkbox" '.$this->convertAttr($val['attr']).'>
                        <label for="'.$id.'Checkbox">'.$val['value'].'</label>
                    </div>
                ';
            }
        }

        return $return;

    }

}

?>
