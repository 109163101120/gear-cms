<?php

class module {

    protected $app;

    public $name;
    public $path;

    protected $config = [];
    public $options = [];

    public function __construct($app, $args = []) {

        $this->app = $app;

        $this->name = $args['name'];
        $this->path = $args['path'];

        $this->config = $args['config'];
        $this->options = $args;

    }

    public function run() {

        $run = $this->options['run'];

        if($run instanceof \Closure) {
            $run = $run->bindTo($this, $this);
        }

        if(is_callable($run)) {
            return call_user_func($run, $this->app);
        }

    }

    public function autoload() {

        if(isset($this->options['autoload'])) {
            if(is_string($this->options['autoload'])) {
                $this->options['autoload'] = (array)$this->options['autoload'];
            }
            foreach((array)$this->options['autoload'] as $dir) {
                autoload::addDir($this->path.'/'.$dir);
            }
        }

    }

    public function config($key = null) {

        if($key) {
            if(isset($this->config[$key])) {
                return $this->config[$key];
            }
            return false;
        }

        return $this->config;

    }

    public function action() {

        if(isset($this->options['action'])) {
            if(is_string($this->options['action'])) {
                $this->options['action'] = (array)$this->options['action'];
            }
            foreach((array)$this->options['action'] as $hook => $callback) {
                if($callback instanceof \Closure) {
                    $callback = $callback->bindTo($this, $this);
                }
                $this->app->hook->add_action($hook, $callback);
            }
        }

    }

    public function filter() {

        if(isset($this->options['filter'])) {
            if(is_string($this->options['filter'])) {
                $this->options['filter'] = (array)$this->options['filter'];
            }
            foreach((array)$this->options['filter'] as $hook => $callback) {
                if($callback instanceof \Closure) {
                    $callback = $callback->bindTo($this, $this);
                }
                $this->app->hook->add_filter($hook, $callback);
            }
        }

    }

}

?>
