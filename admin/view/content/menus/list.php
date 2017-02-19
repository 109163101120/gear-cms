<?php
    admin::addButton('
        <button class="button" @click="addMenuModal = true">
            '.lang::get('add').'
        </button>
    ');

    admin::addComponent('
    <template id="item-template">
        <li :data-id="model.id">
            <div class="entry clear">
                <div class="info">
                    <span>{{ model.name }}</span>
                    <small>{{ model.pageName }} {{ model.pageURL }}</small>
                </div>
                <a :href="\''.url::admin('content', ['menus', 'delItem']).'/\' + model.id + \'/\' + model.children.length" class="icon delete ajax icon-ios-trash-outline"></a>
            </div>
            <ul>
                <item v-if="model.children" v-for="model in model.children" :model="model"></item>
            </ul>
        </li>
    </template>
    ');

    $form = new form();
    $form->setHorizontal(false);

    $form->addFormAttribute('v-on:submit.prevent', 'addMenu');

    $field = $form->addTextField('name', '');
    $field->fieldName(lang::get('name'));
    $field->addAttribute('v-model', 'menuName');
    $field->fieldValidate();

?>

<modal v-if="addMenuModal" @close="addMenuModal = false">
    <h3 slot="header"><?=lang::get('add'); ?></h3>
    <div slot="content">
        <?=$form->show(); ?>
    </div>
</modal>

<div class="columns">

    <div class="lg-3 md-4">

        <aside id="aside">

            <nav v-if="menuID">
                <ul>
                    <li v-for="menu in menus" :class="menu.id == menuID ? 'active' : ''">
                        <a @click.prevent="setMenu(menu.id)">
                            {{ menu.name }}
                        </a>
                        <div class="action">
                            <a class="delete" :href="'<?=url::admin('content', ['menus', 'delete']); ?>/' + menu.id">
                                <i class="icon icon-ios-trash-outline"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

        </aside>

    </div>

    <div class="lg-9 md-8">

        <div v-for="menu in menus">
            <div v-if="menuID == menu.id">
                <div id="menuList">
                    <ul>
                        <item v-for="model in items" :model="model"></item>
                    </ul>
                </div>
            </div>
        </div>

        <div v-show="menuID">

            <hr>

            <div class="box">
                <h3><?=lang::get('add'); ?></h3>
                <?php

                    $form = new form();

                    $form->addFormAttribute('v-on:submit.prevent', 'addMenuItem');

                    $field = $form->addTextField('name', '');
                    $field->fieldName(lang::get('name'));
                    $field->addAttribute('v-model', 'menuItemName');
                    $field->fieldValidate();

                    $form->addTab(lang::get('page'));

                    $field = $form->addRawField('<searchbox :list="pageAll" val="name" id="id"></searchbox>');
                    $field->fieldName(lang::get('page'));
                    $field->fieldValidate();

                    $form->addTab(lang::get('link'));

                    $field = $form->addTextField('link', '');
                    $field->fieldName(lang::get('link'));
                    $field->addAttribute('v-model', 'menuItemLink');
                    $field->fieldValidate();

                    echo $form->show();

                ?>
            </div>

        </div>

    </div>

</div>

<?php
theme::addJSCode('
    window.addEventListener("touchmove", function() {});

    Vue.component("item", {
        template: "#item-template",
        props: {
            model: Object
        }
    });

    new Vue({
        el: "#app",
        data: {
            headline: lang["menus"],
            menus: [],
            items: [],
            pageAll: '.json_encode(PageModel::getAllFromDb()).',
            addMenuModal: false,
            menuName: "",
            menuID: 0,
            menuItemName: "",
            menuItemPage: "",
            menuItemPageID: 0,
            menuItemLink: ""
        },
        mounted: function() {

            var vue = this;

            vue.fetchMenus();

            $(document).on("fetch", function() {
                vue.fetchItems();
            });

            eventHub.$on("setSearchbox", this.setMenuItemPage);

        },
        methods: {
            fetchMenus: function(id) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['menus']).'",
                    dataType: "json",
                    success: function(data) {
                        vue.menus = data;
                        if(data.length) {
                            vue.setMenu(data[0].id);
                        }
                    }
                });

            },
            fetchItems: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['menus', 'get']).'",
                    dataType: "json",
                    data: {
                        id: vue.menuID
                    },
                    success: function(data) {
                        vue.items = data;
                        vue.$nextTick(function() {
                            vue.setDrag();
                        });
                        setTabs();
                    }
                });

            },
            setDrag: function() {

                var drake = dragula($("#menuList ul").toArray(), {
                    mirrorContainer: $("#menuList")[0]
                });

                drake.on("drop", function(el, target, source, sibling) {
                    $.ajax({
                        method: "POST",
                        url: "'.url::admin('content', ['menus', 'move']).'",
                        data: {
                            array: getChildren("#menuList")
                        }
                    });
                });

            },
            addMenu: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['menus', 'add']).'",
                    data: {
                        name: vue.menuName
                    },
                    success: function(data) {
                        vue.addMenuModal = false;
                        vue.fetchMenus();
                        vue.menuName = "";
                    }
                });

            },
            setMenuItemPage: function(data) {
                this.menuItemPageID = data.id;
                this.menuItemPage = data.name;
            },
            addMenuItem: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['menus', 'addItem']).'",
                    data: {
                        id: vue.menuID,
                        name: vue.menuItemName,
                        pageID: vue.menuItemPageID,
                        link: vue.menuItemLink
                    },
                    success: function(data) {
                        vue.fetchItems();
                        vue.menuItemName = "";
                        vue.menuItemLink = "";
                        eventHub.$emit("setSearchboxEmpty");
                    }
                });

            },
            setMenu: function(id) {
                this.menuID = id;
                this.fetchItems();
            }
        }
    });
');
?>
