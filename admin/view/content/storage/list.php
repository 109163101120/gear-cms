<section id="storage">

    <header>

        <h2><?=lang::get('storage'); ?></h2>

        <div class="search">
            <input type="text" v-model="searchString">
        </div>

        <nav>
            <ul>
                <li>
                    <a href="/admin/content/storage/add" class="button">
                        <?=lang::get('upload'); ?>
                    </a>
                </li>
            </ul>
        </nav>

    </header>

    <data-table :data="tableData" :columns="tableColumns" :filter-key="searchString"></data-table>

</section>
