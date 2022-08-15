<?php

get_header();
?>

    <section class="section">
        <div class="container">
            <h1 class="title">
                <?php the_title(); ?>
            </h1>
            <article class="article">
                <?php the_content(); ?>
            </article>
        </div>
    </section>

<?php
get_footer();