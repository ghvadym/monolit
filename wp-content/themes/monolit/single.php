<?php
get_header();
$id = get_the_ID();
?>

    <section class="services_main">
        <div class="container">
            <h1 class="services_main_title">
                <?php the_title(); ?>
            </h1>
            <div class="servises_description active">
                <?php the_content(); ?>
            </div>
        </div>
    </section>

<?php
get_footer();