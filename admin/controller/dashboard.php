<?php

class dashboardController extends controller {

    public function index() {

        theme::addCSS('https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.css');
        theme::addJS('https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js');
        theme::addJS(url::assets('js/calendar.js'));
        theme::addJS(url::assets('js/tooltip.js'));
        theme::addJS(url::assets('js/pointlabel.js'));

        include(dir::view('dashboard/index.php'));

    }

}

?>
