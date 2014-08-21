<?php
/*
  Plugin Name: Custom Editor CSS
  Plugin URI: http://wordpress.org/extend/plugins/custom-editor-css/
  Description: Allows the user to easily apply (CSS) styles and classes to text within the visual editor
  Version: 0.1
  Author: Nicolas Dunand
  Author URI: http://www.unil.ch/riset
  Author Email: nicolas.dunand@unil.ch
  License: GPL v3

  Copyright 2014 Université de Lausanne http://www.unil.ch

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class CustomCss {

    public function __construct() {

        //Plugin menu
        add_action('admin_menu', array(&$this, 'setup_theme_admin_menus'));

        //Add custom styles into editor
        add_filter('mce_buttons_2', 'mce_buttons_2');
        add_filter('tiny_mce_before_init', 'tiny_mce_before_init');
    }

    function setup_theme_admin_menus() {
        add_menu_page('Custom CSS', 'Custom CSS', 'manage_options', 'editor_css', array(&$this, 'customcssclasses_settings'));
    }

    function customcssclasses_settings() {

        if (isset($_POST['update'])) {
            $elements = array();
            $max_id = esc_attr($_POST["element-max-id"]);
            for ($i = 0; $i < $max_id; $i++) {

                $style_name = "style-id-" . $i;
                $style_selector = "selector-id-" . $i;
                $class_name = "class-id-" . $i;
                $style_css = "style-css-" . $i;

                if (isset($_POST[$style_name]) && strlen(trim($_POST[$style_name]))) {
                    $elements[esc_attr($_POST[$style_name])] = array(
                        esc_attr($_POST[$class_name]),
                        esc_attr($_POST[$style_css]),
                        esc_attr($_POST[$style_selector])
                    );
                }
            }
            update_option("customcssclasses", $elements);
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Classes list updated</strong></p></div>';
        }

        $styles = get_option("customcssclasses");
        $styles_counter = sizeof($styles);
        ?>  
        <div class="wrap">  
            <?php screen_icon('themes'); ?> <h2>Custom CSS Classes</h2>  
            <form method="post" action="">  
                <ul id="styles-list">  

                    <?php
                    $styles = get_option("customcssclasses");
                    $styles[''] = '';
                    $style_counter = 0;
                    
                    if($styles) {
                        foreach ($styles as $style => $value) {
                            $class = $value[0];
                            $css = $value[1];
                            $selector = $value[2];
                            ?>
                            <li class="styles-element" id="styles-element-<?php echo $style_counter; ?>">  

                                <label for="style-id-<?php echo $style_counter; ?>">Name:</label>
                                <input name="style-id-<?php echo $style_counter; ?>" type="text" value="<?php echo $style; ?>" />
                                <label for="selector-id-<?php echo $style_counter; ?>">Selector (optional):</label>
                                <input name="selector-id-<?php echo $style_counter; ?>" type="text" value="<?php echo $selector; ?>" />
                                <label for="class-id-<?php echo $style_counter; ?>">Class:</label>
                                <input name="class-id-<?php echo $style_counter; ?>" type="text" value="<?php echo $class; ?>" />
                                <label for="style-css-<?php echo $style_counter; ?>">CSS:</label>
                                <input name="style-css-<?php echo $style_counter; ?>" type="text" value="<?php echo $css; ?>" />
                                <a href="#" onclick="jQuery(this).closest('.styles-element').remove();">Remove</a>
                            </li>  
                            <?php
                            $style_counter++;
                        }
                    }
                    ?>

                </ul>  

                <input type="hidden" name="element-max-id" value="<?php echo $style_counter; ?>" />
                <input type="hidden" name="update" value="1" />  

                <p>  
                    <input type="submit" value="Save settings" class="button-primary"/>  
                </p> 
            </form>  
        </div>    
        <?php
    }
    
}


function mce_buttons_2($buttons) {
    array_unshift($buttons, 'styleselect');
    return $buttons;
}


function tiny_mce_before_init($settings) {
    $style_formats = array();
    $styles = get_option('customcssclasses');
echo '<pre>'; print_r($styles); echo '</pre>';
    foreach($styles as $name => $value){
        $class = $value[0];
        $css = $value[1];
        $selector = $value[2];
        $format = array(
            'title' => $name
        );
        if (strlen(trim($selector))) {
            $format['selector'] = $selector;
        }
        else {
            $format['inline'] = 'span';
        }
        if (strlen(trim($css))) {
            $thestyles = array();
            $thecss = explode(';', $css);
            foreach ($thecss as $acss) {
                if (!trim($acss)) {
                    continue;
                }
                $cssrule = explode(':', $acss);
                $thestyles[$cssrule[0]] = $cssrule[1];
            }
            $format['styles'] = $thestyles;
        }
        else if (strlen(trim($class))) {
            $format['classes'] = $class;
        }
        $style_formats[] = $format;
echo '<pre>==>'; print_r($format); echo '</pre>';
    }
    $settings['style_formats'] = json_encode($style_formats);
    return $settings;
}

new CustomCss();


