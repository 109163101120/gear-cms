<?php

class userController extends controller {

    public function __construct() {
        $this->model = new UserModel;
    }

    public function index($action = '', $id = 0) {

        if($action == 'add') {

            include(dir::view('user/add.php'));

        } elseif($action == 'edit') {

            $id = ($id) ? $id : user::current()->id;

            $model = new UserModel($id);

            include(dir::view('user/edit.php'));

        } else {

            if(ajax::is()) {
                ajax::addReturn(json_encode(UserModel::getAllFromDb()));
            }

            include(dir::view('user/list.php'));

        }

    }

    public function permissions() {

        include(dir::view('user/permissions/list.php'));

    }

}

?>
