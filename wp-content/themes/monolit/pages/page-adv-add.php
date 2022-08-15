<?php
/*
  * Template name: Manage adv
  */
get_header();
?>

    <section class="section">
        <div class="container">
            <h1 class="title">
                <?php _e('Add new advertisement'); ?>
            </h1>
            <div class="adv-form">
                <progress value="0" max="3" id="adv-progress" class="adv-form__progress"></progress>
                <form method="post" enctype="multipart/form-data" id="adv-form">
                    <div class="adv-form__row">
                        <input type="text" name="title" id="title" placeholder="Title" minlength="5" required>
                    </div>
                    <div class="adv-form__row">
                        <input type="email" name="email" id="email" placeholder="Email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" required>
                    </div>
                    <div class="adv-form__row">
                        <input type="file" name="image" id="image" placeholder="Image" accept="image/*" required>
                    </div>
                    <button id="adv-submit" type="submit" class="btn">
                        <?php _e('Submit', 'adv'); ?>
                    </button>
                    <p class="message success hide">
                        <?php _e('Adv has been inserted.'); ?>
                    </p>
                    <p class="message warning hide">
                        <?php _e('Adv has not been inserted.'); ?>
                    </p>
                </form>
            </div>

            <div class="cards" id="cards-wrapper"></div>
        </div>
    </section>

<?php
get_footer();
