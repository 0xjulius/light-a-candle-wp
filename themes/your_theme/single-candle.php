<?php
$candle_cemetery = get_post_meta(get_the_ID(), 'candle_cemetery', true);

switch ($candle_cemetery) {
    case 'Sankarihautausmaa':
        $cemetery_picture_url = 'https://www.lumivaara.fi/wp-content/uploads/2023/07/Kynttila_sankari.jpg';
        break;
    case 'Luterilainen hautausmaa':
        $cemetery_picture_url = 'https://www.lumivaara.fi/wp-content/uploads/2023/07/kynttila_luterilainen.jpg';
        break;
    case 'Ortodoksinen hautausmaa':
        $cemetery_picture_url = 'https://www.lumivaara.fi/wp-content/uploads/2023/07/kynttila_ord.jpg';
        break;
    default:
        $cemetery_picture_url = 'URL_TO_DEFAULT_PICTURE';
        break;
}
?>

<?php
get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content text-center">
                <div class="candle-info-container">

                    <?php
                    $candle_cemetery = get_post_meta(get_the_ID(), 'candle_cemetery', true);
                    $candle_relative_name = get_post_meta(get_the_ID(), 'candle_relative_name', true);
                    $your_name = get_post_meta(get_the_ID(), 'your_name', true);
                    $your_email = get_post_meta(get_the_ID(), 'your_email', true);
                    $candle_duration = get_post_meta(get_the_ID(), 'candle_duration', true);
                    $candle_message = get_post_meta(get_the_ID(), 'candle_message', true);
                    $candle_picture = get_the_post_thumbnail(get_the_ID(), 'full');
                    ?>

                    <div class="candle-details">
                        <?php if ($candle_relative_name) : ?>
                            <p class="candle-relative-name"><?php echo '<strong>' . __("Omaisen nimi:", 'candle') . '</strong> ' . esc_html($candle_relative_name); ?></p>
                        <?php endif; ?>

                        <?php if ($candle_message) : ?>
                            <p class="single-candle-message"><?php echo '<strong>' . __('', 'candle') . '</strong><br><i>' . esc_html($candle_message) . '</i></p>'; ?></p>
                        <?php endif; ?>

                        <?php if ($your_name) : ?>
                            <p class="candle-name-single"><?php echo '<strong>' . __('Kynttilän sytyttäjä:', 'candle') . '</strong> ' . esc_html($your_name); ?></p>
                        <?php endif; ?>


                    </div>

                    <?php if (empty($candle_picture)) :
                        $candle_picture = '<img src="https://www.lumivaara.fi/wp-content/uploads/2023/08/candle.png" alt="Kynttilä">';
                    endif; ?>

                    <div class="single-candle-picture-container">
                        <div class="single-candle-picture">
                            <?php
                            // Display the candle picture
                            echo $candle_picture;
                            ?>
                            <?php
                            $candle_duration = get_post_meta(get_the_ID(), 'candle_duration', true);
                            $candle_end_timestamp = strtotime("+$candle_duration days", get_the_time('U'));
                            $current_timestamp = current_time('timestamp');
                            $remaining_seconds = $candle_end_timestamp - $current_timestamp;

                            if ($remaining_seconds > 0) {
                                $remaining_days = floor($remaining_seconds / (60 * 60 * 24));
                                $remaining_hours = floor(($remaining_seconds % (60 * 60 * 24)) / (60 * 60));
                                $remaining_minutes = floor(($remaining_seconds % (60 * 60)) / 60);

                                echo '<p class="candle-remaining-time"><strong>Palamisaikaa jäljellä:<br></strong> ' . esc_html($remaining_days) . ' päivää, ' . esc_html($remaining_hours) . ' tuntia ja ' . esc_html($remaining_minutes) . ' minuuttia</p>';
                            }
                            ?>
                        </div>
                        <div class="single-cemetery-picture">
                            <?php
                            // Display the cemetery picture
                            echo '<img src="' . esc_url($cemetery_picture_url) . '" alt="Hautausmaa" width="300" height="300">';
                            ?>
                            <?php if ($candle_cemetery) : ?>
                                <p class="single-candle-cemetery"><?php echo '<strong>' . __('Hautausmaa:<br>', 'candle') . '</strong> ' . esc_html($candle_cemetery); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="social-media-sharing">
                        <p>Jaa tämä kynttilä sosiaalisessa mediassa:</p>
                        <ul>
                            <li>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" rel="noopener noreferrer">
                                    Facebook
                                </a>
                            </li>
                            <li>
                                <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>&text=<?php the_title(); ?>" target="_blank" rel="noopener noreferrer">
                                    Twitter
                                </a>
                            </li>
                            <li>
                                <a href="https://www.linkedin.com/shareArticle?url=<?php the_permalink(); ?>&title=<?php the_title(); ?>" target="_blank" rel="noopener noreferrer">
                                    LinkedIn
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- "Näytä kaikki kynttilät" button -->
                    <div class="show-all-candles-button-container">
                        <?php
                        $all_candles_page_url = get_permalink(get_page_by_path('sytyta-muistokynttila'));
                        echo '<a href="' . esc_url($all_candles_page_url) . '" class="show-all-candles-button">Näytä kaikki kynttilät</a>';
                        ?>
                    </div>

                </div>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->

<?php
    endwhile;
endif;

get_footer();
?>

<style>
    @media (min-width: 768px) {
        .single-candle-picture-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 350px;
            margin-bottom: 100px;
            margin-top: 100px;
        }

        .single-candle-picture,
        .single-cemetery-picture {
            width: 350px;
            height: 350px;
            margin-top: 0px;
        }

        .single-candle-picture {
            width: 350px;
            height: 350px;
        }

        .single-candle-picture-container {
            flex-direction: row;
            height: 300px;
        }

        .social-media-sharing {
            margin-top: 150px;
        }

    }
</style>