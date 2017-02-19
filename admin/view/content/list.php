<?php
    admin::addButton('
        <a href="'.url::admin('content', ['grid']).'" class="button border">
            '.lang::get('grid_templates').'
        </a>
    ');

    admin::addButton('
        <a @click="addPageModal = true" class="button">
            '.lang::get('add').'
        </a>
    ');

    admin::addComponent('
    <template id="item-template">
        <li>
            <div class="entry clear">
                <div class="info" :data-id="model.id">
                    <span>{{ model.name }}</span>
                    <small>{{ model.siteURL }}</small>
                </div>
                <span v-if="model.home" class="setHome" data-tooltip="'.lang::get('page_home').'"><i class="icon icon-ios-home"></i></span>
                <span v-else class="setHome" @click="setHome(model.id)"><i class="inactive icon icon-ios-home-outline"></i></span>
                <a :href="\''.url::admin('content', ['index', 'delete']).'/\' + model.id" class="icon delete ajax icon-ios-trash-outline"></a>
                <a :href="\''.url::admin('content', ['index', 'edit']).'/\' + model.id" class="icon edit icon-edit"></a>
            </div>
            <ul v-if="model.children">
                <item v-for="model in model.children" :model="model"></item>
            </ul>
        </li>
    </template>
    ');

    $form = new form();
    $form->setHorizontal(false);

    $form->addFormAttribute('v-on:submit.prevent', 'addPage');

    $field = $form->addTextField('name', '');
    $field->fieldName(lang::get('name'));
    $field->addAttribute('v-model', 'pageName');
    $field->fieldValidate();

    $field = $form->addRawField('<searchbox :list="pageAll" val="name" id="id"></searchbox>');
    $field->fieldName(lang::get('page_parent'));

    $field = $form->addSelectField('content', '');
    $field->fieldName(lang::get('grid_template'));
    $field->addAttribute('v-model', 'pageGrid');
    $field->add(0, lang::get('no_template'));
    foreach(GridModel::getAll() as $val) {
        $field->add($val['id'], $val['name']);
    }

?>

<modal v-if="addPageModal" @close="addPageModal = false">
    <h3 slot="header"><?=lang::get('add'); ?></h3>
    <div slot="content">
        <?=$form->show(); ?>
    </div>
</modal>

<div id="pageList" class="box">
    <h3><?=option::get('sitename'); ?></h3>
    <ul>
        <item v-for="model in pageTree" :model="model"></item>
    </ul>
    <template v-if="!pageTreeLength">
        <?=lang::get('no_results'); ?>
    </template>
</div>

<?php
theme::addJSCode('

    Vue.component("item", {
        template: "#item-template",
        props: {
            model: Object
        },
        methods: {
            setHome: function(id) {
                eventHub.$emit("setHome", id);
            }
        }
    });

    new Vue({
        el: "#app",
        data: {
            headline: lang["pages"],
            addPageModal: false,
            pageName: "",
            pageParent: 0,
            pageGrid: 0,
            pageParentName: "",
            pageTree: '.json_encode(PageModel::getAll()).',
            pageAll: '.json_encode(PageModel::getAllFromDb()).',
            searchBoxShow: false,
            searchBox: ""
        },
        mounted: function() {

            var vue = this;

            $(document).on("fetch", function() {
                vue.fetch();
            });

            eventHub.$on("setHome", this.setHome);
            eventHub.$on("setSearchbox", this.setParent);

            vue.dragdrop();

        },
        methods: {
            fetch: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'get']).'",
                    dataType: "json",
                    success: function(data) {
                        vue.pageAll = data.all;
                        vue.pageTree = data.tree;
                        vue.dragdrop();
                    }
                });

            },
            dragdrop: function() {

                var vue = this;

                $(document).ready(function() {
                    $("#pageList .entry > .info").draggable({
                        revert: "invalid",
                        helper: "clone",
                        containment: $("#pageList")
                    });
                    $("#pageList .entry > .info, #pageList > h3").droppable({
                        hoverClass: "dropActive",
                        drop: function(e, ui) {
                            var drag = $(ui.draggable);
                            var drop = $(this);
                            vue.move(drop.data("id"), drag.data("id"));
                        }
                    });
                });

            },
            setParent: function(data) {
                this.pageParent = data.id;
                this.pageParentName = data.name;
            },
            addPage: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'add']).'",
                    data: {
                        name: vue.pageName,
                        parent: vue.pageParent,
                        grid: vue.pageGrid
                    },
                    success: function(data) {
                        vue.fetch();
                        vue.addPageModal = false;
                        vue.pageName = "";
                        vue.pageGrid = 0;
                        eventHub.$emit("setSearchboxEmpty");
                    }
                });

            },
            move: function(parentID, id) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'move']).'",
                    data: {
                        parent: parentID,
                        id: id
                    },
                    success: function() {
                        vue.fetch();
                    }
                });

            },
            setHome: function(id) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'setHome']).'",
                    data: {
                        id: id
                    },
                    success: function() {
                        vue.fetch();
                    }
                });

            }
        },
        computed: {
            pageTreeLength: function() {
                return !jQuery.isEmptyObject(this.pageTree);
            }
        }
    });
');
?>
