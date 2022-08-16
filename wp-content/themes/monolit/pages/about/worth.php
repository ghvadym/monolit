<?php
if (empty($fields)) {
    return;
}
?>

<section class="our_values">
    <?php
    titleHtml($fields['home_worth_title']);
    getField($fields['home_worth_subtitle'], 'our_values_suptitle', 'p');
    getField($fields['home_worth_text'], 'our_values_text', 'p');
    if ($fields['home_worth_list']): ?>

        <div class="our_values_second">
            <div class="our_values_container">
                <div class="our_values_content">
                    <?php foreach ($fields['home_worth_list'] as $item): ?>
                        <div class="our_values_item">
                            <?php if ($item['img']): ?>
                                <div class="value_item_icon">
                                    <img src="<?php echo $item['img']; ?>" alt="Photo">
                                </div>
                            <?php endif; ?>

                            <?php
                            getField($item['title'], 'value_item_title_blu', 'p');
                            getField($item['subtitle'], 'value_item_title', 'p');
                            getField($item['text'], 'value_item_text', 'p');
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</section>