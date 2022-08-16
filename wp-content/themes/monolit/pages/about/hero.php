<?php
if (empty($fields)) {
    return;
}

$thumbnail = get_the_post_thumbnail_url();
?>

<section class="about_us" style="background-image: url(<?php echo $fields['home_top_img']; ?>);">
    <?php breadcrumbs(); ?>
    <h1 class="about_us_title">
        <?php the_title(); ?>
    </h1>
    <div class="about_us_container">
        <div class="block_about_left">
            <?php getField($fields['home_title'], 'block_about_history', 'p'); ?>
            <?php getField($fields['home_short_description'], 'block_about_text', 'p'); ?>

            <?php if ($thumbnail): ?>
                <div class="block_about_photo_container">
                    <img src="<?php echo $thumbnail; ?>" alt="Photo">
                </div>
            <?php endif; ?>
        </div>
        <?php getField(get_the_content(), 'block_about_right'); ?>
    </div>
</section>