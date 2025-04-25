<?php
/*
Plugin Name: Favorite Sites Links
Description: Плагин для отображения списка любимых сайтов с категориями, описанием и настройками
Version: 1.7
Author: Andrew Arutunyan & Grok
*/

// Регистрируем меню настроек
function fsl_register_settings() {
    add_options_page(
        'Настройки любимых сайтов',
        'Любимые сайты',
        'manage_options',
        'favorite-sites-links',
        'fsl_settings_page'
    );
}
add_action('admin_menu', 'fsl_register_settings');

// Регистрируем опции
function fsl_register_options() {
    register_setting('fsl_options_group', 'fsl_links');
    register_setting('fsl_options_group', 'fsl_columns', array('default' => '2'));
    register_setting('fsl_options_group', 'fsl_target', array('default' => '_blank'));
    register_setting('fsl_options_group', 'fsl_heading_level', array('default' => 'h3'));
}
add_action('admin_init', 'fsl_register_options');

// Страница настроек
function fsl_settings_page() {
    ?>
    <div class="wrap">
        <h1>Любимые сайты</h1>
        <form method="post" action="options.php">
            <?php settings_fields('fsl_options_group'); ?>
            <h3>Список сайтов</h3>
            <textarea name="fsl_links" rows="25" cols="200"><?php echo esc_textarea(get_option('fsl_links')); ?></textarea>
            <p>Формат: Категория | Название сайта | URL | Описание (описание необязательно)</p>
            <p>Пример:<br>
            Поиск | Google | https://google.com | Лучшая поисковая система<br>
            Энциклопедии | Wikipedia | https://wikipedia.org</p>
            
            <h3>Настройки отображения</h3>
            <p>
                <label>Количество колонок:</label><br>
                <input type="radio" name="fsl_columns" value="1" <?php checked('1', get_option('fsl_columns')); ?>> Одна колонка<br>
                <input type="radio" name="fsl_columns" value="2" <?php checked('2', get_option('fsl_columns')); ?>> Две колонки
            </p>
            <p>
                <label>Открывать ссылки:</label><br>
                <input type="radio" name="fsl_target" value="_self" <?php checked('_self', get_option('fsl_target')); ?>> В том же окне<br>
                <input type="radio" name="fsl_target" value="_blank" <?php checked('_blank', get_option('fsl_target')); ?>> В новом окне
            </p>
            <p>
                <label>Уровень заголовка категорий:</label><br>
                <select name="fsl_heading_level">
                    <option value="h1" <?php selected('h1', get_option('fsl_heading_level')); ?>>H1</option>
                    <option value="h2" <?php selected('h2', get_option('fsl_heading_level')); ?>>H2</option>
                    <option value="h3" <?php selected('h3', get_option('fsl_heading_level')); ?>>H3</option>
                    <option value="h4" <?php selected('h4', get_option('fsl_heading_level')); ?>>H4</option>
                    <option value="h5" <?php selected('h5', get_option('fsl_heading_level')); ?>>H5</option>
                    <option value="h6" <?php selected('h6', get_option('fsl_heading_level')); ?>>H6</option>
                </select>
            </p>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Шорткод для вывода ссылок
function fsl_display_links($atts) {
    $links = get_option('fsl_links');
    if (!$links) return '<p>Список сайтов пуст</p>';
    
    $columns = get_option('fsl_columns', '2');
    $target = get_option('fsl_target', '_blank');
    $heading_level = get_option('fsl_heading_level', 'h3');
    $lines = explode("\n", trim($links));
    $categories = [];
    
    // Группируем ссылки по категориям
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) >= 3) {
            $category = trim($parts[0]);
            $name = trim($parts[1]);
            $url = trim($parts[2]);
            $description = isset($parts[3]) ? trim($parts[3]) : '';
            $categories[$category][] = [
                'name' => $name,
                'url' => $url,
                'description' => $description
            ];
        }
    }
    
    $output = '<div class="favorite-sites" data-columns="' . esc_attr($columns) . '">';
    
    if ($columns == '2') {
        $total_categories = count($categories);
        $half = ceil($total_categories / 2);
        $left_column = array_slice($categories, 0, $half, true);
        $right_column = array_slice($categories, $half, null, true);
        
        $output .= '<div class="fsl-column fsl-left">';
        foreach ($left_column as $category => $links) {
            $output .= sprintf('<%s>%s</%s>', $heading_level, esc_html($category), $heading_level);
            $output .= '<ul class="favorite-sites-list">';
            foreach ($links as $link) {
                $description = $link['description'] ? ' — ' . esc_html($link['description']) : '';
                $output .= sprintf(
                    '<li><a href="%s" target="%s" rel="noopener noreferrer">%s</a>%s</li>',
                    esc_url($link['url']),
                    esc_attr($target),
                    esc_html($link['name']),
                    $description
                );
            }
            $output .= '</ul>';
        }
        $output .= '</div>';
        
        $output .= '<div class="fsl-column fsl-right">';
        foreach ($right_column as $category => $links) {
            $output .= sprintf('<%s>%s</%s>', $heading_level, esc_html($category), $heading_level);
            $output .= '<ul class="favorite-sites-list">';
            foreach ($links as $link) {
                $description = $link['description'] ? ' — ' . esc_html($link['description']) : '';
                $output .= sprintf(
                    '<li><a href="%s" target="%s" rel="noopener noreferrer">%s</a>%s</li>',
                    esc_url($link['url']),
                    esc_attr($target),
                    esc_html($link['name']),
                    $description
                );
            }
            $output .= '</ul>';
        }
        $output .= '</div>';
    } else {
        foreach ($categories as $category => $links) {
            $output .= sprintf('<%s>%s</%s>', $heading_level, esc_html($category), $heading_level);
            $output .= '<ul class="favorite-sites-list">';
            foreach ($links as $link) {
                $description = $link['description'] ? ' — ' . esc_html($link['description']) : '';
                $output .= sprintf(
                    '<li><a href="%s" target="%s" rel="noopener noreferrer">%s</a>%s</li>',
                    esc_url($link['url']),
                    esc_attr($target),
                    esc_html($link['name']),
                    $description
                );
            }
            $output .= '</ul>';
        }
    }
    
    $output .= '</div>';
    return $output;
}
add_shortcode('favorite_sites', 'fsl_display_links');

// Добавляем стили
function fsl_add_styles() {
    wp_enqueue_style(
        'fsl-styles',
        plugin_dir_url(__FILE__) . 'fsl-styles.css'
    );
}
add_action('wp_enqueue_scripts', 'fsl_add_styles');