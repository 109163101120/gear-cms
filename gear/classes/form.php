<?php

class form {

    protected $method;
    protected $action;

    protected $formAttributes = [];

    protected $toSave = [];
    protected $return = [];

    protected $fields = [];

    protected $errors = [];

    public static $rules = [];

    public function __construct($action = '', $method = 'post') {

        $this->method = $method;
        $this->action = $action;

        $this->addFormAttribute('action', $this->action);
        $this->addFormAttribute('method', $this->method);
        $this->addFormAttribute('class', 'horizontal');

    }

    private function addElement($name, $object, $save = true) {

        if($save) {
            $this->toSave[$name] = $object;
        }

        $this->return[$name] = $object;

        return $object;

    }

    private function addField($name, $value, $class, $attributes = [], $save = true) {

        $field = new $class($name, type::super($name, '', $value), $attributes);
        $this->addElement($name, $field, $save);

        return $field;

    }

    public function addTextField($name, $value, $attributes = []) {

        $attributes['type'] = 'text';

        return $this->addField($name, $value, 'formInput', $attributes);

    }

    public function addPasswordField($name, $value, $attributes = []) {

        $attributes['type'] = 'password';

        return $this->addField($name, $value, 'formInput', $attributes);

    }

    public function addRadioField($name, $value, $attributes = []) {
        return $this->addField($name, $value, 'formRadio', $attributes);
    }

    public function addCheckboxField($name, $value, $attributes = []) {
        return $this->addField($name, $value, 'formCheckbox', $attributes);
    }

    public function addSwitchField($name, $value, $attributes = []) {

        $attributes['switch'] = true;

        return $this->addField($name, $value, 'formCheckbox', $attributes);

    }

    public function addHiddenField($name, $value, $attributes = []) {

        $attributes['type'] = 'hidden';

        return $this->addField($name, $value, 'formInput', $attributes);

    }

    public function addSelectField($name, $value, $attributes = []) {
        return $this->addField($name, $value, 'formSelect', $attributes);
    }

    public function addTextareaField($name, $value, $attributes = []) {
        return $this->addField($name, $value, 'formTextarea', $attributes);
    }

    public function addFormAttribute($name, $value) {

        if(isset($this->formAttributes[$name])) {
            $this->formAttributes[$name] = $this->formAttributes[$name].' '.$value;
        } else {
            $this->formAttributes[$name] = $value;
        }

        return $this;

    }

    public function getElement($name) {
        return $this->return[$name];
    }

    public function deleteElement($name) {

        unset($this->return[$name]);

        return $this;

    }

    public function getAll() {

        $return = [];

        foreach($this->toSave as $name => $object) {

            $value = type::super($name);

            $return[$name] = (is_array($value)) ? implode('|', $value) : $value;

        }

        return $return;

    }

    public static function addRule($name, $rule) {
        self::$rules[$name] = $rule;
    }

    public static function getRules() {
        return self::$rules;
    }

    public function isSubmit() {
        return type::post('save', 'bool', false);
    }

    public function validation() {

        $valid = validate($this->getAll(), $this->getRules());

        if($valid[0]) {
            return true;
        } else {
            $this->errors[] = $valid[1];
        }

        return false;

    }

    public function getErrors() {

        if(count($this->errors)) {
            return implode(PHP_EOL, $this->errors);
        } else {
            return '';
        }

    }

    public function show() {

        $return = [];
		$hidden = [];

        $return[] = '<form'.html_convertAttribute($this->formAttributes).'>'.PHP_EOL;

        foreach($this->return as $show) {

			if($show->getAttribute('type') == 'hidden') {
				$hidden[] = $show->get();
				continue;
			}

			$return[] = '<div class="form-element">';
			$return[] = '<label class="sm-3" for="'.$show->getAttribute('id').'">'.$show->fieldName.'</label>';
			$return[] = '<div class="sm-9">'.$show->get().'</div>';
			$return[] = '</div>';

		}

        $return[] = '
            <button class="button fl-right" name="save" type="submit">'.lang::get('save').'</button>
            <div class="clear"></div>
        ';

        $return[] = implode(PHP_EOL, $hidden);

        $return[] = '</form>'.PHP_EOL;

        return implode(PHP_EOL, $return);

    }

}

?>
