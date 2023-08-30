<?php header('Content-Type: text/html; charset=utf-8');
/**
 * Plugin Name: Sytytä kynttilä
 * Description: Mahdollistaa käyttäjien sytyttää muistokynttilän edesmenneille läheisilleen.
 * Version: 1.3
 * Author: JuliusAalto
 * Author URI: www.juliusaalto.com
 * Information: Tämä lisäosa on tarkoitettu vain yksityiskäyttöön, tai tekijän määrittämille yhteistyökumppaneille. Kaikki oikeudet pidätetään.
 * Tätä lisäosaa tai mitään sen osaa ei saa kopioida, muokata, levittää tai esittää julkisesti ilman tekijänoikeuden omistajan etukäteen antamaa kirjallista lupaa.
 * Tämän laajennuksen luvaton kopiointi tai käyttö voi johtaa oikeudellisiin toimiin.
 */

// Luodaan mukautettu postaus tyyppi
function candle_create_post_type()
{
    $labels = array(
        'name' => __('Sytytä kynttilä', 'candle'),
        'singular_name' => __('Candle', 'candle'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'thumbnail', 'editor'),
    );

    register_post_type('candle', $args);
}
add_action('init', 'candle_create_post_type');

// Näytetään kynttilän tiedot metaboxissa
function candle_render_meta_box($post)
{
    // Haetaan tallenetut arvot, jos ne ovat saatavilla
    $candle_cemetery = get_post_meta($post->ID, 'candle_cemetery', true);
    $candle_relative_name = get_post_meta($post->ID, 'candle_relative_name', true);
    $candle_duration = get_post_meta($post->ID, 'candle_duration', true);
    $your_name = get_post_meta($post->ID, 'your_name', true);
    $your_email = get_post_meta($post->ID, 'your_email', true);
    $candle_message = get_post_meta($post->ID, 'candle_message', true);

    // Kaapataan tiedot HTML-lomakkeesta
?>
    <label for="candle_cemetery"><?php _e('Hautausmaa:', 'candle'); ?></label>
    <input type="text" name="candle_cemetery" id="candle_cemetery" value="<?php echo esc_attr($candle_cemetery); ?>">

    <label for="candle_relative_name"><?php _e("Omaisen nimi:", 'candle'); ?></label>
    <input type="text" name="candle_relative_name" id="candle_relative_name" value="<?php echo esc_attr($candle_relative_name); ?>">

    <label for="candle_duration"><?php _e('Kynttilän kesto (päivinä):', 'candle'); ?></label>
    <input type="number" name="candle_duration" id="candle_duration" value="<?php echo esc_attr($candle_duration); ?>" min="1">

    <label for="your_name"><?php _e('Nimi:', 'candle'); ?></label>
    <input type="text" name="your_name" id="your_name" value="<?php echo esc_attr($your_name); ?>">

    <label for="your_email"><?php _e('Sähköposti:', 'candle'); ?></label>
    <input type="text" name="your_email" id="your_email" value="<?php echo esc_attr($your_email); ?>">

    <label for="candle_message"><?php _e('Viesti:', 'candle'); ?></label>
    <textarea name="candle_message" id="candle_message" rows="5"><?php echo esc_textarea($candle_message); ?></textarea>

<?php
}

// Tallennetaan kynttilän tiedot postaukseen
function candle_save_submission($post_id)
{
    // Käyttäjän IP-osoitteen haku postauksesta
    $user_ip = isset($_SERVER['REMOTE_ADDR']);

    // Tallennetaan IP-osoite
    update_post_meta($post_id, 'user_ip', $user_ip);

    // Tarkistetaan, onko kyseessä automaattinen tallennus vai tarkistus.
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Hautausmaa
    if (isset($_POST['candle_cemetery'])) {
        $candle_cemetery = sanitize_text_field($_POST['candle_cemetery']);
        update_post_meta($post_id, 'candle_cemetery', $candle_cemetery);
    }
    // Kynttilän kesto
    if (isset($_POST['candle_duration'])) {
        $candle_duration = absint($_POST['candle_duration']);
        update_post_meta($post_id, 'candle_duration', $candle_duration);
    }

    // Läheisen nimi
    if (isset($_POST['candle_relative_name'])) {
        $candle_relative_name = sanitize_text_field($_POST['candle_relative_name']);
        update_post_meta($post_id, 'candle_relative_name', $candle_relative_name);
    }

    // Sinun nimi
    if (isset($_POST['your_name'])) {
        $your_name = sanitize_text_field($_POST['your_name']);
        update_post_meta($post_id, 'your_name', $your_name);
    }

    // Email
    if (isset($_POST['your_email'])) {
        $your_email = sanitize_email($_POST['your_email']);
        update_post_meta($post_id, 'your_email', $your_email);
    }

    // Viesti
    if (isset($_POST['candle_message'])) {
        $candle_message = sanitize_textarea_field($_POST['candle_message']);
        update_post_meta($post_id, 'candle_message', $candle_message);
    }
}

add_action('save_post', 'candle_save_submission');

// Luodaan post-editori metaboksille
function candle_add_meta_box()
{
    add_meta_box('candle_meta_box', __('Kynttilän tiedot:', 'candle'), 'candle_render_meta_box', 'candle', 'normal', 'default');
}
add_action('add_meta_boxes', 'candle_add_meta_box');

//Luodaan käyttäjille mahdollisuus luoda uusi kynttilä ja haetaan tiedot
function light_a_candle_shortcode($atts)
{
    ob_start(); // Tuodaan HTML-sisältö


    if (isset($_POST['candle_submit'])) {   // Tarkistetaan onko käyttäjä lähettänyt kynttilän, jos on, niin tiedot tallennetaan
        if (isset($_POST['privacy_policy_checkbox']) && $_POST['privacy_policy_checkbox'] === 'on') {
            $candle_cemetery = isset($_POST['candle_cemetery']) ? sanitize_text_field($_POST['candle_cemetery']) : '';
            $candle_relative_name = isset($_POST['candle_relative_name']) ? sanitize_text_field($_POST['candle_relative_name']) : '';
            $your_name = isset($_POST['your_name']) ? sanitize_text_field($_POST['your_name']) : '';
            $your_email = isset($_POST['your_email']) ? sanitize_text_field($_POST['your_email']) : '';
            $candle_message = isset($_POST['candle_message']) ? sanitize_text_field($_POST['candle_message']) : '';
            $candle_duration = isset($_POST['candle_duration']) ? intval($_POST['candle_duration']) : 7; // Oletusarvoinen kesto 7 päivää

        } else {
            echo '<p class="error-message">' . __('Sinun on hyväksyttävä tietosuojakäytäntö ennen lomakkeen lähettämistä.', 'candle') . '</p>';
        }


        // Luodaan uusi kynttiläpostaus, oletusarvoisena kynttilät menevät luonnokseksi, jotta ne voidaan erikseen tarkistaa.
        $candle_args = array(
            'post_title' => $candle_relative_name,
            'post_type' => 'candle',
            'post_status' => 'draft'
        );

        $candle_id = wp_insert_post($candle_args);

        // Tallennetaan kenttien sisältö
        update_post_meta($candle_id, 'candle_cemetery', $candle_cemetery);
        update_post_meta($candle_id, 'candle_relative_name', $candle_relative_name);
        update_post_meta($candle_id, 'your_name', $your_name);
        update_post_meta($candle_id, 'your_email', $your_email);
        update_post_meta($candle_id, 'candle_message', $candle_message);
        update_post_meta($candle_id, 'candle_duration', $candle_duration);


        // Näytetään käyttäjälle palaute kynttilän lähettämisen yhteydessä.
        echo '<p class="light-a-candle-success-message custom-success-message">' . __('Kynttilä lähetetty onnistuneesti! <br>Ylläpidolla saattaa olla viivettä kynttilän hyväksymisessä, hyväksymisaika on normaalisti 1-3 päivää!', 'candle') . '</p>';
    }

    //  Kynttilän lähetyslomake, HTML-elementit/frontend
?>
    <div class="light-a-candle-container">
        <form class="light-a-candle-form" method="post" action="">
            <label for="your_name"><?php _e('', 'candle'); ?></label>
            <div class="user-icon"><i class="fas fa-user"></i></div>
            <input type="text" placeholder="Sinun nimesi.." name="your_name" id="your_name" required>

            <label for="your_email"><?php _e('', 'candle'); ?></label>
            <div class="email-icon"><i class="fas fa-envelope"></i></div>
            <input type="email" placeholder="Sähköpostiosoitteesi.." name="your_email" id="your_email" required>

            <label for="candle_cemetery"><?php _e('', 'candle'); ?></label>
            <div class="cemetery-icon"><i class="fas fa-landmark"></i></div>
            <select class="select" name="candle_cemetery" id="candle_cemetery" required>
                <option value="" disabled selected hidden>Valitse hautausmaa</option>
                <option value="Sankarihautausmaa"><?php _e('Sankarihautausmaa', 'candle'); ?></option>
                <option value="Luterilainen hautausmaa"><?php _e('Luterilainen hautausmaa', 'candle'); ?></option>
                <option value="Ortodoksinen hautausmaa"><?php _e('Ortodoksinen hautausmaa', 'candle'); ?></option>
            </select>

            <label for="candle_relative_name"><?php _e("", 'candle'); ?></label>
            <div class="love-icon"><i class="fas fa-heart"></i></div>
            <input type="text" placeholder="Omaisesi nimi.." name="candle_relative_name" id="candle_relative_name" required>


            <label for="candle_message"><?php _e('Kirjoita jotain mitä haluat kertoa:', 'candle'); ?></label>
            <textarea name="candle_message" id="candle_message" rows="5"></textarea>

            <label for="candle_duration"><?php _e('Kynttilän kesto päivissä:', 'candle'); ?></label>
            <div class="candle_duration-text"><?php _e('(hyväksymisen kesto n. 1-3 pv).', 'candle'); ?></div>
            <input type="number" name="candle_duration" id="candle_duration" value="7" min="1" max="7" required>

            <input type="submit" name="candle_submit" value="Lähetä">

            <label for="privacy_policy_checkbox">
                <input type="checkbox" name="privacy_policy_checkbox" id="privacy_policy_checkbox" required>
                <?php
                $privacy_policy_url = get_site_url() . '/yleista';
                _e('Olen lukenut ja hyväksyn seuraavat ehdot', 'candle');
                echo ' <a href="' . esc_url($privacy_policy_url) . '" target="_blank">' . __('Tietosuojakäytäntö', 'candle') . '</a>.';
                ?>
            </label>
    </div>
    </form>
    </div>
<?php

    return ob_get_clean();
}

function show_current_candles_shortcode($atts) //Näytetään nykyiset (julkaistut) kynttilät
{
    ob_start();

    $candles_query = new WP_Query(array(
        'post_type' => 'candle',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ));

    // Haetaan nykyisten kynttilöiden tiedot
    if ($candles_query->have_posts()) {
        while ($candles_query->have_posts()) {
            $candles_query->the_post();
            $candle_id = get_the_ID();
            $candle_title = get_the_title();
            $candle_cemetery = get_post_meta($candle_id, 'candle_cemetery', true);
            $candle_relative_name = get_post_meta($candle_id, 'candle_relative_name', true);
            $your_name = get_post_meta($candle_id, 'your_name', true);
            $your_email = get_post_meta($candle_id, 'your_email', true);
            $candle_duration = get_post_meta($candle_id, 'candle_duration', true);
            $candle_message = get_post_meta($candle_id, 'candle_message', true);
            $candle_picture = '<img src="https://www.lumivaara.fi/wp-content/uploads/2023/08/candle.png" alt="Kynttilä">';
            $cemetery_picture_url = '';

            // Hautausmaiden kuvien automatiikka
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
                    // Default hautausmaa ?
                    $cemetery_picture_url = 'URL_TO_DEFAULT_PICTURE';
                    break;
            }
            // Varmistetaan, että kynttilän vähimmäiskesto on 1 päivä (86400 sekuntia).
            $candle_duration = max($candle_duration, 1);
            $candle_end_timestamp = strtotime("+$candle_duration days", get_the_time('U', $candle_id));
            $current_timestamp = current_time('timestamp');
            $remaining_seconds = $candle_end_timestamp - $current_timestamp;

            // Tarkistetaan, että kynttilä ei ole sammunut
            if ($remaining_seconds > 0) {
                $remaining_days = floor($remaining_seconds / (60 * 60 * 24));
                $remaining_hours = floor(($remaining_seconds % (60 * 60 * 24)) / (60 * 60));
                $remaining_minutes = floor(($remaining_seconds % (60 * 60)) / 60);
                $remaining_time = sprintf(_n('%1$d päivä', '%1$d päivää', $remaining_days, 'light-a-candle'), $remaining_days);
                $remaining_time .= sprintf(', %1$d tuntia', $remaining_hours);
                $remaining_time .= sprintf(', %1$d minuuttia', $remaining_minutes);

                // Nykyisten kynttilöiden HTML-elementit
                echo '<div class="candle-container">';
                echo '<div class="candle-item">';
                echo '<h4 class="candle-title"><a href="' . esc_url(get_permalink($candle_id)) . '">' . esc_html($candle_title) . '</a></h4>';
                echo '<p class="candle-message"><i>' . esc_html($candle_message) . '</i></p>';

                // Näytä kynttilän kuva
                echo '<div class="candle-picture-container">';
                echo '<div class="candle-picture">' . $candle_picture;
                echo '<p class="candle-duration"><strong>Palamisaikaa jäljellä:<br></strong> ' . esc_html($remaining_time) . '</p>';
                echo '</div>';

                // Näytä hautausmaan kuva
                echo '<div class="cemetery-picture">';
                echo '<img src="' . esc_url($cemetery_picture_url) . '" alt="Cemetery Picture" width="300" height="300">';
                echo '<p class="candle-cemetery"><strong>Hautausmaa:<br></strong> ' . esc_html($candle_cemetery) . '</p>';
                echo '</div>';
                echo '</div>';

                echo '<p class="candle-name"><strong>Kynttilän sytyttäjä:</strong> ' . esc_html($your_name) . '</p>';
                echo '</div>';
                echo '</div>';
            }
        }
    } else {
        echo '<p>Kynttilöitä ei löydetty.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}

function light_a_candle_enqueue_scripts()
{
    wp_enqueue_style('light-a-candle-style', plugins_url('light-a-candle/light-a-candle.css')); // Tuodaan tyylitiedosto
}
add_action('wp_enqueue_scripts', 'light_a_candle_enqueue_scripts');

add_shortcode('show_current_candles', 'show_current_candles_shortcode');                        // Luodaan shortcode ominaisuus WordPressiin

add_shortcode('light_a_candle', 'light_a_candle_shortcode');                                    // Luodaan shortcode ominaisuus WordPressiin


function candle_add_settings_link($links) // Lisätään lisäosan asetukset linkki
{
    $settings_link = '<a href="options-general.php?page=candle_settings">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'candle_add_settings_link');


// Lisäosan asetukset -sivu
function candle_settings_page()
{
    add_options_page(
        __('Sytytä kynttilä', 'candle'),
        __('Sytytä kynttilä', 'candle'),
        'manage_options',
        'candle_settings',
        'candle_settings_page_content'
    );

    // Lisää alivalikkosivu
    add_submenu_page(
        'edit.php?post_type=candle',
        __('Asetukset', 'candle'),
        __('Asetukset', 'candle'),
        'manage_options',
        'candle_settings',
        'candle_settings_page_content'
    );
}



// Näytetään asetussivun sisältö
function candle_settings_page_content()
{
    // Tarkistetaan adminoikeudet
    if (!current_user_can('manage_options')) {
        return;
    }

    // Käsittele asetukset valikon lähetys
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['candle_email_confirmation_enabled'])) {
            update_option('candle_email_confirmation_enabled', 1);
        } else {
            update_option('candle_email_confirmation_enabled', 0);
        }

        if (isset($_POST['candle_confirmation_email'])) {
            update_option('candle_confirmation_email', sanitize_email($_POST['candle_confirmation_email']));
        }

        if (isset($_POST['candle_send_email_to_users'])) {
            update_option('candle_send_email_to_users', 1);
        } else {
            update_option('candle_send_email_to_users', 0);
        }
    }

    //Luodaan front-end asetukset-sivulle
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="">
            <?php settings_fields('candle_settings_group'); ?>
            <?php do_settings_sections('candle_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Sähköposti-ilmoitukset', 'candle'); ?></th>
                    <td>
                        <label for="candle_email_confirmation_enabled">
                            <input type="checkbox" name="candle_email_confirmation_enabled" id="candle_email_confirmation_enabled" value="1" <?php checked(get_option('candle_email_confirmation_enabled', false), 1); ?>>
                            <?php _e('Ota uusien kynttilöiden ilmoitukset käyttöön', 'candle'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Sähköpostiosoite', 'candle'); ?></th>
                    <td>
                        <input type="email" name="candle_confirmation_email" id="candle_confirmation_email" value="<?php echo esc_attr(get_option('candle_confirmation_email', get_option('admin_email'))); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Sähköposti-ilmoitukset käyttäjille', 'candle'); ?></th>
                    <td>
                        <label for="candle_send_email_to_users">
                            <?php $candle_send_email_to_users = get_option('candle_send_email_to_users', true); ?>
                            <input type="checkbox" name="candle_send_email_to_users" id="candle_send_email_to_users" value="1" <?php checked($candle_send_email_to_users, 1); ?>>
                            <?php _e('Lähetä vahvistusviesti käyttäjälle, kun kynttilä julkaistaan.', 'candle'); ?>
                        </label>
                    </td>
                </tr>


            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

// Luodaan custom post type
add_action('init', 'candle_create_post_type');

function candle_additional_page_content()
{
?>
    <div class="wrap">
        <h1><?php echo esc_html__('Additional Page', 'candle'); ?></h1>
    </div>
<?php
}

// Luo lisäosan pääasetussivu ja alivalikkosivu
add_action('admin_menu', 'candle_settings_page');

function candle_register_settings() // Asetusten rekisteröinti
{
    register_setting('candle_settings_group', 'candle_email_confirmation_enabled', 'intval');
    register_setting('candle_settings_group', 'candle_confirmation_email', 'sanitize_email');
    register_setting('candle_settings_group', 'candle_send_email_to_users', 'intval');
}
add_action('admin_init', 'candle_register_settings');



// Lähetään kynttilän vahvistusviesti sähköpostilla
function send_candle_confirmation_email($candle_id)
{
    // Tarkistetaan onko kynttilä jo julkaistu
    $post_status = get_post_status($candle_id);
    if ($post_status === 'publish') {
        $send_email_to_users = get_option('candle_send_email_to_users', true);
        if ($send_email_to_users) {
            // Get the user's email
            $your_email = get_post_meta($candle_id, 'your_email', true);

            // Lähetetään kävijälle vahvistusviesti
            $to = $your_email;
            $subject = __('Kynttiläsi on julkaistu!', 'candle');
            $candle_link = get_permalink($candle_id);
            $site_name = get_bloginfo('name');
            $message = sprintf(__('Onnittelut! Kynttiläsi on nyt sytytetty %s -sivustolla, katso se täällä: <a href="%s">%s</a>', 'candle'), esc_html($site_name), esc_url($candle_link), esc_url($candle_link));
            $message .= '<p>Ystävällisesti: ' . $site_name . '</p>';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($to, $subject, $message, $headers);
        }
    } else {
        // Uuden kynttilän vastaanotto, ilmoitetaan adminille sähköpostitse
        $email_enabled = get_option('candle_email_confirmation_enabled', false);
        if ($email_enabled) {
            $to = get_option('candle_confirmation_email', get_option('admin_email'));
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $subject = __('Uusi kynttilä vastaanotettu!', 'candle');
            $edit_link = home_url('/wp-admin/post.php?post=' . $candle_id . '&action=edit');
            $site_name = get_bloginfo('name');
            $message = sprintf(__('Uusi kynttilä vastaanotettu %s -sivustolle, ole hyvä ja tarkista se täältä: <a href="%s">%s</a>', 'candle'), esc_html($site_name), esc_url($edit_link), esc_url($edit_link));

            // Käyttäjän IP-osoitteen lisääminen viestiin
            $message .= '<p>Kynttilä tuli seuraavasta IP-osoitteesta: ' . $user_ip . '</p>';

            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Lähetä sähköpostiviesti ylläpidolle
            wp_mail($to, $subject, $message, $headers);
        }
    }
}

add_action('save_post_candle', 'send_candle_confirmation_email');
