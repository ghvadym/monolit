<?php
$title = get_field('faq_title', 'options');
$btn = get_field('faq_btn', 'options');
$items = get_field('faq_list', 'options');

if (empty($items)) {
    return;
}
?>

<section class="asking">
    <?php titleHtml($title); ?>

    <?php foreach ($items as $item): ?>
        <div class="asking_item">
            <div class="ask_arrow_container"></div>
            <div class="ask_text_content">
                <h3 class="question">
                    <?php echo $item['question']; ?>
                </h3>
                <p class="answer active">
                    <?php echo $item['answer']; ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($btn)): ?>
        <section class="order_question">
            <a href="<?php echo $btn['url']; ?>" target="<?php echo $btn['target']; ?>" class="order_servises_question">
                <?php echo $btn['title']; ?>
            </a>
        </section>
    <?php endif; ?>
</section>