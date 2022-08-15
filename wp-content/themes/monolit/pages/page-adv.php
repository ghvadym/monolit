<?php
/*
 * Template name: Adv
 */

get_header();
$paged = get_query_var('paged') ?: 1;
$postsPerPage = get_option('posts_per_page') ?: 12;

$query = new WP_Query(
    [
        'posts_per_page' => $postsPerPage,
        'post_type'      => 'adv',
        'post_status'    => 'publish',
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'desc',
    ]
);

$paginationArgs = [
    'current'   => $paged,
    'total'     => $query->max_num_pages,
    'prev_text' => '<',
    'next_text' => '>',
];
?>

    <section class="section">
        <div class="container">
            <h1 class="title">
                <?php the_title(); ?>
            </h1>
            <?php if ($query->have_posts()): ?>
                <div class="cards">
                    <?php while ($query->have_posts()):
                        $query->the_post();
                        get_template_part('pages/parts/card', '', $query->post);
                    endwhile; ?>
                </div>
            <?php endif; ?>
            <div class="pagination-links">
                <?php echo paginate_links($paginationArgs); ?>
            </div>
        </div>
    </section>

<?php
get_footer();