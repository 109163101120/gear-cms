Vue.filter("lang", function(value) {
    if(value in lang) {
        return lang[value];
    } else {
        return value;
    }
});

Vue.component("table-cell", {
    template: '<td :class="class"><slot></slot></td>',
    props: {
        class: ''
    }
});

Vue.component("data-table", {
    template: "#table-template",
    props: {
        data: [],
        columns: [],
        showSearch: true,
        headline: '',
        filterKey: ''
    },
    data: function() {
        var sortOrders = {};
        this.columns.forEach(function(key) {
            sortOrders[key] = -1;
        });
        return {
            checked: [],
            sortKey: '',
            currentPage: 0,
            perPage: 8,
            resultCount: 0,
            oldHeadline: '',
            sortOrders: sortOrders
        };
    },
    created: function() {
        this.oldHeadline = this.headline;
    },
    watch: {
        checked: function() {
            this.$dispatch("checked", this.checked);
        },
        headline: function() {
            this.$dispatch("headline", {
                headline: this.headline,
                showSearch: this.showSearch
            });
        }
    },
    computed: {
        checkAll: {
            get: function() {
                return this.data ? this.checked.length == this.data.length &&  this.data.length > 0 : false;
            },
            set: function(value) {
                var checked = [];

                if(value) {
                    this.data.forEach(function(loop) {
                        checked.push(loop.id);
                    });
                }

                this.checked = checked;
            }
        },
        columnSpan: function() {
            return this.columns.length + 1;
        },
        filtered: function() {
            return this.$eval("data | filterBy filterKey");
        },
        totalPages: function() {
            return Math.ceil(this.resultCount / this.perPage);
        }
    },
    methods: {
        sortBy: function(key) {
            if(!this.sortKey) {
                this.sortKey = key;
                this.sortOrders[key] = 1;
            } else if(this.sortOrders[key] == 1) {
                this.sortOrders[key] = -1;
            } else {
                this.sortKey = '';
                this.sortOrders[key] = 0;
            }
        },
        setPage: function(page) {
            this.currentPage = page;
        }
    },
    events: {
        checked: function(data) {
            if(data.length) {
                this.headline = data.length + " " + lang["selected"] + "<a href='?delete=" + this.checked + "' class='icon delete icon-ios-trash-outline'></a>";
                this.showSearch = false;
            } else {
                this.headline = this.oldHeadline;
                this.showSearch = true;
            }
        }
    },
    filters: {
        paginate: function(list) {

            this.resultCount = list.length;

            if (this.currentPage >= this.totalPages) {
                this.currentPage = this.totalPages - 1;
            }

            var index = this.currentPage * this.perPage;

            return list.slice(index, index + this.perPage);

        }
    }
});

Vue.component("file-table", {
    template: "#file-table-template",
    props: {
        data: [],
        columns: [],
        showSearch: true,
        headline: "",
        filterKey: "",
        path: "/"
    },
    data: function() {
        return {
            oldHeadline: "",
            checked: []
        };
    },
    created: function() {

        this.oldHeadline = this.headline;

        if($.session.get("fileTablePath")) {
            this.path = $.session.get("fileTablePath");
        }

        var vue = this;

        $(document).on("fetch", function() {
            vue.fetch();
        });

    },
    watch: {
        checked: function() {
            this.$dispatch("checked", this.checked);
        },
        headline: function() {
            this.$dispatch("headline", {
                headline: this.headline,
                showSearch: this.showSearch
            });
        },
        path: function() {
            this.$dispatch("path", this.path);
            $.session.set("fileTablePath", this.path);
            this.fetch();
        }
    },
    computed: {
        checkAll: {
            get: function() {
                return this.data ? this.checked.length == this.data.length &&  this.data.length > 0 : false;
            },
            set: function(value) {
                var checked = [];

                if(value) {
                    this.data.forEach(function(loop) {
                        checked.push(loop.id);
                    });
                }

                this.checked = checked;
            }
        },
        breadcrumbs: function() {

            var path = "";
            var str = "";

            if(this.path) {
                str = this.path.split("/").filter(function(str) {
                    return str.length;
                }).map(function(part) {
            	    return {
                        path: path += "/" + part + "/",
                        name: part
                    };
                });
            }

            return str;

        },
        filtered: function() {
            return this.$eval("data | filterBy filterKey");
        }
    },
    methods: {
        fetch: function() {

            this.checked = [];

            var vue = this;

            $.ajax({
                method: "POST",
                url: url + "admin/content/media/get",
                dataType: "json",
                data: {
                    path: vue.path
                },
                success: function(data) {
                    vue.$set("data", data);
                }
            });

        },
        setPath: function(path) {
            this.checked = [];
            this.$set("path", path);
        },
        move: function(path, dropdata) {

            var vue = this;

            $.ajax({
                method: "POST",
                url: url + "admin/content/media/move",
                data: {
                    path: path,
                    file: dropdata.id,
                    name: dropdata.name
                },
                success: function() {
                    vue.fetch();
                }
            });

        }
    },
    events: {
        checked: function(data) {
            if(data.length) {
                this.headline = data.length + " " + lang["selected"] + "<a href='" + url + "admin/content/media/?delete=" + this.checked + "' class='icon delete ajax icon-ios-trash-outline'></a>";
                this.showSearch = false;
            } else {
                this.headline = this.oldHeadline;
                this.showSearch = true;
            }
        },
        fetchData: function() {
            this.fetch();
        }
    }
});

Vue.transition("fade", {
    enterClass: "fadeInDown",
    leaveClass: "fadeOutUp"
});

Vue.component("modal", {
    template: "#modal-template",
    props: {
        show: {
            type: Boolean,
            required: true,
            twoWay: true
        }
    },
    watch: {
        show: function() {
            if(this.show) {
                $("#overlay").fadeIn(200);
            } else {
                $("#overlay").fadeOut(200);
            }
        }
    }
});
