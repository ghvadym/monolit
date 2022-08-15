<?php
$id = get_field('faq_form_id', 'options');

if (!$id) {
    return;
}
?>

<div class="popap_question">
    <span class="close_arrow"></span>
    <?php echo do_shortcode('[wpforms id="'. $id .'"]'); ?>
</div>