<?php

add_action('wp_ajax_search_form', 'submitForm');
add_action('wp_ajax_nopriv_search_form', 'submitForm');

function submitForm()
{
    $input = $_POST['input'] ?? null;

    if (!$input) {
        return;
    }

    $args = [
        's'                => htmlspecialchars($input),
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'suppress_filters' => false,
    ];

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post(); ?>
            <a class="search-form_data" href="<?php the_permalink($query->post); ?>">
                <?php echo $query->post->post_title; ?>
            </a>
        <?php }
    } else {
        echo '<div class="search-form_empty">' . __('Empty') . '</div>';
    }

    $html = ob_get_contents();
    ob_end_clean();

    wp_send_json([
        'result' => $html,
    ]);
}