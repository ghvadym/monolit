<?php
$title = get_field('gallery_title', 'options');
$images = get_field('gallery', 'options');

if (empty($images)) {
    return;
}
?>

<section id="gallery-section" class="gallery_section">
    <div class="container">
        <?php titleHtml($title); ?>


        <div class="carousel carousel_gallery" data-gallery="">
            <button class="carousel-button prev gallery_prev_btn" data-carousel-button="prev"></button>
            <button class="carousel-button next gallery_next_btn" data-carousel-button="next"></button>
            <div class="slides" data-slides="">

                <?php foreach ($images as $index => $image):
                    $photoName = __('Фотография', 'monolit') . ' ';?>
                    <div class="slideGallery">
                        <div class="gallery_slide_position">
                            <a class="prev_slide">
                                <img src="<?php echo $images[--$index]; ?>" alt="Фотография 5">
                                <p></p>
                            </a>
                            <a class="current_slide" data-href="<?php echo $image; ?>">
                                <img src="<?php echo $image; ?>" alt="Фотография 1">
                                <p class="photo_name">
                                    <?php echo $photoName . intval($index + 2); ?>
                                </p>
                            </a>
                            <a class="next_slide">
                                <img src="<?php echo $images[++$index]; ?>" alt="Фотография 2">
                                <p></p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</section>