<section id="content">

    <header>

        <h2>{{{ headline | lang }}}</h2>

        <nav>
            <ul>
                <li>
                    <a @click="addPageModal = true" class="button">
                        <?=lang::get('add'); ?>
                    </a>
                </li>
            </ul>
        </nav>

    </header>

    <?php

        $form = new form();
        $form->setHorizontal(false);

        $form->addFormAttribute('v-on:submit.prevent', 'addPage');

        $field = $form->addTextField('name', '');
        $field->fieldName(lang::get('name'));
        $field->addAttribute('v-model', 'pageName');
        $field->fieldValidate();

        $field = $form->addSelectField('parentID', '');
        $field->fieldName(lang::get('page_parent'));
        $field->addAttribute('v-model', 'pageParent');

        $field->add(0, lang::get('page_parent_no'));
        foreach(PageModel::getAllFromDb() as $page) {
            $field->add($page->id, $page->name);
        }

    ?>

    <modal :show.sync="addPageModal">
        <h3 slot="header"><?=lang::get('add'); ?></h3>
        <div slot="content">
            <?=$form->show(); ?>
        </div>
    </modal>

    <div id="pageList">
        <div v-drop="move(0, $dropdata)"><?=lang::get('page'); ?></div>
        <ul>
            <item v-for="model in pageTree" :model="model"></item>
        </ul>
    </div>

</section>

<template id="item-template">
    <li>
        <div v-drag="{id: model.id}" v-drop="move(model.id, $dropdata)">{{ model.name }}</div>
        <ul v-if="model.children">
            <item v-for="model in model.children" :model="model"></item>
        </ul>
    </li>
</template>

<?php
theme::addJSCode('
    Vue.component("item", {
        template: "#item-template",
        props: {
            model: Object
        },
        methods: {
            move: function(parentID, data) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'move']).'",
                    data: {
                        parent: parentID,
                        id: data.id
                    },
                    success: function() {
                        vue.$dispatch("eventFetch");
                    }
                });

            }
        }
    });
    new Vue({
        el: "#content",
        data: {
            headline: "pages",
            addPageModal: false,
            pageName: "",
            pageParent: 0,
            pageTree: '.json_encode(PageModel::getAll()).',
            pageAll: '.json_encode(PageModel::getAllFromDb()).'
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
                    }
                });

            },
            addPage: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'add']).'",
                    dataType: "text",
                    data: {
                        name: vue.pageName,
                        parent: vue.pageParent
                    },
                    success: function(data) {
                        vue.fetch();
                        vue.addPageModal = false;
                        vue.pageName = "";
                    }
                });

            },
            move: function(parentID, data) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('content', ['index', 'move']).'",
                    data: {
                        parent: parentID,
                        id: data.id
                    },
                    success: function() {
                        vue.fetch();
                    }
                });

            }
        },
        events: {
            eventFetch: function() {
                this.fetch();
            }
        }
    });
');
?>
