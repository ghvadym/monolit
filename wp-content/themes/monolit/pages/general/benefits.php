<?php
$title = get_field('benefits_title', 'options');
$benefits = get_field('benefits_list', 'options');

if (empty($benefits)) {
    return;
}
?>

<section class="why_us">
    <div class="container">
        <?php titleHtml($title); ?>
        <div class="why_us_container">
            <?php foreach ($benefits as $index => $benefit): ?>
                <div class="why_us_item">
                    <h3 class="why_us_item_title">
                        <?php echo $index <= 9 ? 0 . ++$index : ++$index; ?>
                    </h3>
                    <p class="why_us_item_text">
                        <?php echo $benefit['text']; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>