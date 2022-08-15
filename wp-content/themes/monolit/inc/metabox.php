<?php

add_action('add_meta_boxes', 'add_custom_meta_boxes');
add_action('save_post', 'save_post_data');

function add_custom_meta_boxes()
{
    add_meta_box(
        'adv_custom_attachment',
        'Adv Image',
        'adv_attachment_call',
        'adv',
        'side'
    );
}

function adv_attachment_call($post)
{
    $imgId = get_post_meta($post->ID, 'adv_attachment_id', true) ?? '';
    $imgUrl = wp_get_attachment_image_url($imgId, 'medium') ?? '';
    ?>

    <div class="adv-thumbnail">
        <p id="adv-thumbnail-item">
            <?php if ($imgUrl): ?>
                <img src="<?php echo $imgUrl; ?>" alt="<?php echo get_the_title($imgId); ?>" width="100%" height="200" style="object-fit:contain">
            <?php endif; ?>
        </p>

        <span id="adv-attachment-add" class="button">
            <?php _e('Add image', 'adv'); ?>
        </span>

        <?php if ($imgUrl): ?>
            <span id="adv-attachment-remove" class="button">
                <?php _e('Remove image', 'adv'); ?>
            </span>
        <?php endif; ?>

        <input type="hidden" id="adv-attachment-id" class="button" name="adv-attachment-id" value="<?php echo $imgId; ?>">
    </div>
    <?php
}

function save_post_data($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['adv-attachment-id'])) {
        update_post_meta($post_id, 'adv_attachment_id', $_POST['adv-attachment-id'] ?? '');
    }
}