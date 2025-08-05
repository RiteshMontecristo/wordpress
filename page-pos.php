<head>
    <?php wp_head(); ?>
</head>

<header>
    <h1>MONTECRISTO POS</h1>
</header>

<main class="pos">
    <section class="pos-search">
        <div>

            <form class="pos-search-form" id="pos-search-form">
                <input placeholder="Search with product name or SKU" name="search-product" id="search-product" type="text" />
                <button>Search</button>
            </form>
        </div>

        <div class="search-results" id="search-results">
        </div>

    </section>

    <section>
    <form id="pos-email-form">

    <button>Send Email</button>
    </form>
    </section>


</main>

<?php wp_footer() ?>