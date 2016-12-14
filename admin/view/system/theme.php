<section id="themes">
    <div class="columns">
        <div v-for="(entry, key) in themes" class="md-4 sm-6">
            <div class="box">
                <h3>{{ entry.name }}</h3>
                <div class="screenshot" :style="'background-image: url(<?=config::get('url').'themes/'; ?>' + key + '/screenshot.png);'"></div>
                <div v-if="entry.active" class="button active"><?=lang::get('active'); ?></div>
                <a v-else class="button border" @click="setActive(key)"><?=lang::get('activate'); ?></a>
            </div>
        </div>
    </div>
</section>

<?php
theme::addJSCode('
    new Vue({
        el: "#app",
        data: {
            headline: lang["theme"],
            themes: '.json_encode(theme::getAll()).'
        },
        created: function() {

            var vue = this;

            eventHub.$on("setHeadline", function(data) {
                vue.headline = data.headline;
            });

        },
        methods: {
            setActive: function(theme) {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('system', ['theme', 'setActive']).'",
                    data: {
                        theme: theme
                    },
                    success: function() {
                        vue.fetch();
                    }
                });

            },
            fetch: function() {

                var vue = this;

                $.ajax({
                    method: "POST",
                    url: "'.url::admin('system', ['theme', 'get']).'",
                    dataType: "json",
                    success: function(data) {
                        vue.themes = data;
                    }
                });

            }
        }
    });
');
?>
