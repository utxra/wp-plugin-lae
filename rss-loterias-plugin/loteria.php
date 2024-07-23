<ul>

    <?php if (empty($rss_items)) : ?>

        <li>
            <?php _e('No hay elementos.', 'my-text-domain'); ?>

        </li>

    <?php else : ?>

        <li>

            <a href="<?php echo esc_url($item->get_permalink()); ?>" title="<?php printf(__('Publicado el %s', 'my-text-domain'), $item->get_date('j F Y | g:i a')); ?>">
                <?php echo esc_html($item->get_title()); ?></a>

            <p><?php echo $item->get_date('j F Y | g:i a'); ?></p>

            <div><?php echo $item->get_description(); ?></div>

        </li>


    <?php endif; ?>

</ul>