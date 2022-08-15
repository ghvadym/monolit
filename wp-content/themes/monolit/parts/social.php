<?php
$social = [
    'telegram' => 'telegram.png',
    'viber'    => 'viber.png',
    'facebook' => 'fb.webp',
];
?>


<div class="footer_social">
    <?php foreach ($social as $name => $image):
        if (!$item = get_field($name, 'option')) continue; ?>

        <div class="footer_social_link">
            <a href="<?php echo $item; ?>" target="_blank">
                <img src="<?php echo get_template_directory_uri() . '/assets/img/' . $image; ?>" alt="<?php echo $name; ?>">
            </a>
        </div>

    <?php endforeach; ?>
</div>