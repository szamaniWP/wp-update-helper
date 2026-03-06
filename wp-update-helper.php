<?php
/**
 * Plugin Name: Szamaniwp Updates
 * Description: Prosty plugin do sprawdzania statusu pluginów, motywów, WP i PHP przed i po aktualizacji
 * Version: 1.1
 * Author: Szamaniwp
 */

if (!defined('ABSPATH')) exit;

// Dodaj podstronę do menu "Narzędzia"
add_action('admin_menu', 'Szamaniwp_updates_menu');

function Szamaniwp_updates_menu() {
    add_management_page(
        'Szamaniwp Updates',
        'Szamaniwp Updates',
        'manage_options',
        'Szamaniwp-updates',
        'Szamaniwp_updates_page'
    );
}

// Obsługa zapisywania i resetowania
add_action('admin_init', 'Szamaniwp_updates_handle_actions');

function Szamaniwp_updates_handle_actions() {
    if (!isset($_POST['Szamaniwp_action'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_POST['Szamaniwp_nonce']) || !wp_verify_nonce($_POST['Szamaniwp_nonce'], 'Szamaniwp_updates_action')) {
        return;
    }

    $redirect_url = admin_url('tools.php?page=Szamaniwp-updates');

    if ($_POST['Szamaniwp_action'] === 'save') {
        $status_data = Szamaniwp_get_current_status();
        $before = get_option('Szamaniwp_status_before');

        if ($before === false) {
            update_option('Szamaniwp_status_before', $status_data);
            $redirect_url = add_query_arg('Szamaniwp_msg', 'saved_before', $redirect_url);
        } else {
            update_option('Szamaniwp_status_after', $status_data);
            $redirect_url = add_query_arg('Szamaniwp_msg', 'saved_after', $redirect_url);
        }
    }

    if ($_POST['Szamaniwp_action'] === 'reset') {
        delete_option('Szamaniwp_status_before');
        delete_option('Szamaniwp_status_after');
        $redirect_url = add_query_arg('Szamaniwp_msg', 'reset', $redirect_url);
    }

    wp_safe_redirect($redirect_url);
    exit;
}

// Pobierz aktualny status
function Szamaniwp_get_current_status() {
    $status = "PLUGINS:\n";

    $all_plugins    = get_plugins();
    $active_plugins = get_option('active_plugins', array());

    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $active  = in_array($plugin_file, $active_plugins) ? '[aktywny]' : '[nieaktywny]';
        $status .= $plugin_data['Name'] . ' | ' . $plugin_data['Version'] . ' ' . $active . "\n";
    }

    $status .= "THEMES:\n";

    $all_themes = wp_get_themes();
    foreach ($all_themes as $theme) {
        $status .= $theme->get('Name') . ' | ' . $theme->get('Version') . "\n";
    }

    $status .= "WORDPRESS:\n";
    $status .= get_bloginfo('version') . "\n";

    $status .= "PHP:\n";
    $status .= phpversion();

    return $status;
}

// Prosty diff liniowy między dwoma stringami
function Szamaniwp_diff($before, $after) {
    $before_lines = explode("\n", $before);
    $after_lines  = explode("\n", $after);

    $before_set = array_flip($before_lines);
    $after_set  = array_flip($after_lines);

    $output = '';

    // Pokaż linie z "po" — nowe lub niezmienione
    foreach ($after_lines as $line) {
        if (!isset($before_set[$line])) {
            $output .= '<span style="background:#d4edda;display:block;">+ ' . esc_html($line) . '</span>';
        } else {
            $output .= '<span style="display:block;">  ' . esc_html($line) . '</span>';
        }
    }

    // Dołącz linie usunięte (były w "przed", nie ma w "po")
    foreach ($before_lines as $line) {
        if (!isset($after_set[$line])) {
            $output .= '<span style="background:#f8d7da;display:block;">- ' . esc_html($line) . '</span>';
        }
    }

    return $output;
}

// Strona pluginu
function Szamaniwp_updates_page() {
    $current_status = Szamaniwp_get_current_status();
    $before         = get_option('Szamaniwp_status_before');
    $after          = get_option('Szamaniwp_status_after');

    // Komunikaty po redirect
    if (isset($_GET['Szamaniwp_msg'])) {
        $msg = sanitize_key($_GET['Szamaniwp_msg']);
        $messages = array(
            'saved_before' => array('Status PRZED zapisany!', 'updated'),
            'saved_after'  => array('Status PO zapisany!', 'updated'),
            'reset'        => array('Status zresetowany!', 'updated'),
        );
        if (isset($messages[$msg])) {
            echo '<div class="notice notice-' . $messages[$msg][1] . ' is-dismissible"><p>' . esc_html($messages[$msg][0]) . '</p></div>';
        }
    }

    // Etykieta przycisku zależna od stanu
    if ($before === false) {
        $button_label = 'Zapisz status PRZED';
    } elseif ($after === false) {
        $button_label = 'Zapisz status PO';
    } else {
        $button_label = 'Nadpisz status PO';
    }
    ?>
    <div class="wrap">
        <h1>Szamaniwp Updates</h1>

        <h2>Aktualny Status</h2>
        <pre style="background:#f5f5f5;padding:15px;border:1px solid #ddd;"><?php echo esc_html($current_status); ?></pre>

        <form method="post">
            <?php wp_nonce_field('Szamaniwp_updates_action', 'Szamaniwp_nonce'); ?>
            <input type="hidden" name="Szamaniwp_action" value="save">
            <p>
                <input type="submit" class="button button-primary" value="<?php echo esc_attr($button_label); ?>">
            </p>
        </form>

        <?php if ($before !== false): ?>
            <hr>
            <h2>Status PRZED</h2>
            <pre style="background:#fff8e5;padding:15px;border:1px solid #ddd;"><?php echo esc_html($before); ?></pre>
        <?php endif; ?>

        <?php if ($after !== false): ?>
            <hr>
            <h2>Status PO</h2>
            <pre style="background:#e5f8e5;padding:15px;border:1px solid #ddd;"><?php echo esc_html($after); ?></pre>
        <?php endif; ?>

        <?php if ($before !== false && $after !== false): ?>
            <hr>
            <h2>Zmiany (PRZED → PO)</h2>
            <pre style="background:#f5f5f5;padding:15px;border:1px solid #ddd;font-family:monospace;"><?php echo Szamaniwp_diff($before, $after); ?></pre>
        <?php endif; ?>

        <?php if ($before !== false || $after !== false): ?>
            <hr>
            <form method="post" onsubmit="return confirm('Na pewno chcesz zresetować zapisane dane?');">
                <?php wp_nonce_field('Szamaniwp_updates_action', 'Szamaniwp_nonce'); ?>
                <input type="hidden" name="Szamaniwp_action" value="reset">
                <p>
                    <input type="submit" class="button button-secondary" value="Reset">
                </p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
