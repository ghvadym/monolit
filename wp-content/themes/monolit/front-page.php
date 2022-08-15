<?php
/*
 * Template name: Головна
 */
get_header();
$post = get_post();
$fields = get_fields($post->ID);
$options = get_fields('options');

$services = get_terms([
    'taxonomy'   => 'services_categories',
    'hide_empty' => false,
    'orderby'    => 'meta_value_num',
    'meta_key'   => 'service_order',
    'meta_type'  => 'NUMERIC',
    'order'      => 'asc',
]);

get_template_part_var('pages/home/hero', [
    'services' => $services,
]);

get_template_part_var('pages/home/services', [
    'services' => $services,
]);

get_template_part_var('pages/general/benefits');

get_template_part_var('pages/general/gallery');

get_template_part_var('pages/general/contact-form');

get_template_part_var('pages/general/blog');

get_template_part_var('pages/general/partners');

get_template_part_var('pages/general/faq');

get_template_part_var('pages/general/certificates');

?>

    <section class="article_main">
        <div class="container">
            <div class="article_content">
                <?php the_content(); ?>
            </div>
        </div>
    </section>

<?php
get_footer();