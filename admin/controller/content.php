<?php

class contentController extends controller {

    public function __construct() {

    }

    public function index($action = '', $id = 0, $method = '') {

        $this->model = new PageModel;

        if(ajax::is()) {

            $id = type::post('id', 'int', $id);
            $parentID = type::post('parent', 'int', 0);

            if($action == 'get') {

                $return = [
                    'tree' => PageModel::getAll(),
                    'all' => PageModel::getAllFromDb()
                ];

                ajax::addReturn(json_encode($return));

            } elseif($action == 'move') {

                function saveNested($array, $i, $parent = 0) {
                    if($array && is_array($array)) {
                        foreach($array as $val) {
                            if(isset($val['id'])) {
                                $itemModel = new PageModel($val['id']);
                                $values = [
                                    'parentID' => $parent,
                                    'order' => $i
                                ];
                                $itemModel->save($values);
                                $i++;
                                saveNested($val['children'], 1, $val['id']);
                            }
                        }
                    }
                }

                saveNested(type::post('array', 'array', []), 1, 0);

                message::success(lang::get('page_moved'));

            } elseif($action == 'setHome') {

                if($id) {
                    if(option::set('home', $id)) {
                        message::success(lang::get('page_home_set'));
                    }
                }

            } elseif($action == 'add') {

                $name = type::post('name', 'string', '');
                $grid = type::post('grid', 'int', 0);

                if($name) {

                    $insert = [
                        'name'=> $name,
                        'parentID' => $parentID,
                        'siteURL' => filter::url($name)
                    ];

                    if($grid) {
                        $grid = new GridModel($grid);
                        if($grid->content) {
                            $insert['content'] = $grid->content;
                        }
                    }

                    $this->model->insert($insert, true);

                    message::success(lang::get('page_added'));

                } else {
                    message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                }

            } elseif($action == 'delete') {

                extension::add('model_beforeDelete', function($id) {
    			    if($id == option::get('home')) {
                        message::error(lang::get('page_delete_home'));
                        return false;
                    }
    		        return $id;
			    });

                extension::add('model_beforeDelete', function($id) {
                    $where = [
                        'meta_key' => 'parentID',
                        'meta_value' => $id
                    ];
                    if(sql::run()->from('entry')->leftJoin('entry_meta ON id = entry_id')->where($where)->fetch()) {
                        message::error(lang::get('page_is_parent'));
                        return false;
                    }
                    return $id;
			    });

                if($this->model->delete($id)) {
                    message::success(lang::get('page_deleted'));
                }

            }

        }

        if($action == 'edit' && $id) {

            $this->model->load($id);

            $this->gridAjax($id, $method);

            include(dir::view('content/edit.php'));

        } else {

            include(dir::view('content/list.php'));

        }

    }

    public function grid($action = '', $id = 0, $method = '') {

        $this->model = new GridModel;

        if($action == 'edit' && $id) {

            $this->model->load($id);

            $this->gridAjax($id, $method);

            include(dir::view('content/grid/edit.php'));

        } else {

            if(ajax::is()) {
                if($action == 'get') {
                    ajax::addReturn(json_encode(GridModel::getAll()));
                } elseif($action == 'add') {

                    $name = type::post('name', 'string', '');

                    if($name) {

                        $this->model->insert([
                            'name'=> $name
                        ], true);

                        message::success(lang::get('grid_template_added'));

                    } else {
                        message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                    }

                }
            }

            if($action == 'delete') {
                if($this->model->delete($id)) {
                    message::success(lang::get('grid_template_deleted'));
                }
            }

            include(dir::view('content/grid/list.php'));

        }

    }

    public function menus($action = '', $id = 0, $children = 0) {

        $this->model = new MenuModel;

        if(ajax::is()) {

            $id = type::post('id', 'int', $id);

            if($action == 'get') {

                ajax::addReturn(json_encode(MenuItemModel::getAll($id)));

            } elseif($action == 'addItem') {

                $name = type::post('name', 'string', '');
                $pageID = type::post('pageID', 'int', 0);
                $link = type::post('link', 'string', '');

                if($name) {
                    if($pageID || $link) {
                        if($this->model->load($id)->addItem($name, 0, $pageID, $link)) {
                            message::success(lang::get('menu_item_added'));
                        }
                    } else {
                        message::error(lang::get('menu_page_link_required'));
                    }
                } else {
                    message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                }

            } elseif($action == 'delItem') {
                if($id && $children == 0) {
                    if($this->model->load($id)->delete()) {
                        message::success(lang::get('menu_item_deleted'));
                    }
                } else {
                    message::error(lang::get('menu_item_delete_children'));
                }
            } elseif($action == 'add') {

                $name = type::post('name', 'string', '');

                if($name) {

                    $insert = $this->model->insert([
                        'name'=> $name
                    ], true);

                    message::success(lang::get('menu_added'));

                    ajax::addReturn(json_encode($insert));

                } else {
                    message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                }

            } elseif($action == 'edit') {

            } elseif($action == 'move') {

                function saveNested($array, $i, $parent = 0) {
                    if($array && is_array($array)) {
                        foreach($array as $val) {
                            if(isset($val['id'])) {
                                $itemModel = new MenuItemModel($val['id']);
                                $values = [
                                    'parentID' => $parent,
                                    'order' => $i
                                ];
                                $itemModel->save($values);
                                $i++;
                                saveNested($val['children'], 1, $val['id']);
                            }
                        }
                    }
                }

                saveNested(type::post('array', 'array', []), 1, 0);

                message::success(lang::get('menu_item_moved'));

            } else {
                ajax::addReturn(json_encode(MenuModel::getAllFromDb()));
            }

        }

        if($action == 'delete') {
            if($id) {
                if($this->model->load($id)->deleteAllItems()->delete()) {
                    message::success(lang::get('menu_deleted'));
                }
            }
        }

        include(dir::view('content/menus/list.php'));

    }

    public function media($action = '', $file = '') {

        if(ajax::is()) {

            $path = type::post('path', 'string', '');
            $name = type::post('name', 'string', '');
            $file = type::post('file', 'string', $file);

            if($action == 'get') {

                ajax::addReturn(json_encode(media::getAll($path)));

            } elseif($action == 'addDir') {

                if($name) {

                    $path = dir::media($path.filter::file($name));

                    media::addDir($path);

                } else {
                    message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                }

            } elseif($action == 'move') {

                if(media::move($file, $path.$name)) {
                    message::success(lang::get('file_moved'));
                } else {
                    message::error(lang::get('file_not_moved'));
                }

            } elseif($action == 'edit') {

                if($name) {

                    if(media::move($file, $path.filter::file($name))) {
                        message::success(lang::get('file_edited'));
                    } else {
                        message::error(lang::get('file_not_edited'));
                    }

                } else {
                    message::error(sprintf(lang::get('validate_required'), lang::get('name')));
                }

            } elseif($action == 'delete') {

                if($file) {
                    media::delete($file);
                }

            } elseif($action == 'upload') {

                if(media::upload(type::files('file'), $path)) {
                    message::success(lang::get('file_uploaded'));
                } else {
                    message::error(lang::get('file_not_uploaded'));
                }

            }

        }

        include(dir::view('content/media/list.php'));

    }

    public function gridAjax($id, $method) {

        if(ajax::is()) {

            if($method == 'getContent') {

                $return = ($this->model->content) ? $this->model->content : json_encode([], JSON_OBJECT_AS_ARRAY);

                ajax::addReturn($return);

            } elseif($method == 'saveContent') {

                $content = type::post('content', 'array', []);
                $content = (count($content)) ? json_encode($content, JSON_OBJECT_AS_ARRAY) : null;

                if($this->model->save(['content' => $content])) {
                    message::success(lang::get('grid_saved'));
                }

                $return = ($this->model->load($id)->content) ? $this->model->load($id)->content : json_encode([], JSON_OBJECT_AS_ARRAY);

                ajax::addReturn($return);

            } elseif($method == 'orderRows') {

                $content = json_decode($this->model->content);
                $from = type::post('from');
                $to = type::post('to');

                if(count($content) && isset($content[$from]) && isset($content[$to])) {

                    $first = $content[$from];
                    $second = $content[$to];

                    $content[$to] = $first;
                    $content[$from] = $second;

                    $this->model->save(['content' => json_encode($content, JSON_OBJECT_AS_ARRAY)]);

                    message::success(lang::get('grid_saved'));

                }

            }

        }

    }

}

?>
