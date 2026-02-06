<?php

if (!defined('ABSPATH'))
    exit;


if (!class_exists('Aiomatic_Elementor_Assistant'))
{
    final class Aiomatic_Elementor_Assistant 
    {
        private static $instance;
        public $content_generator;
        public $headline_generator;
        public static function instance() 
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Aiomatic_Elementor_Assistant)) 
            {
                self::$instance = new Aiomatic_Elementor_Assistant;
                self::$instance->includes();
                self::$instance->content_generator = new Aiomatic_Content_Generator();
                self::$instance->headline_generator = new Aiomatic_Headline_Generator();
                self::$instance->hooks();
            }
            return self::$instance;
        }

        private function includes() 
        {
            if(!function_exists('is_plugin_active'))
            {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            require_once(dirname(__FILE__) . "/res/elementor/content-generator.php");
            require_once(dirname(__FILE__) . "/res/elementor/headline-generator.php");
        }

        private function hooks() 
        {
            add_action('plugins_loaded', array($this, 'enhancement_hooks'));
        }

        function enhancement_hooks() 
        {
            add_action('elementor/editor/before_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
        }

        public function enqueue_editor_scripts() 
        {

            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('aiomatic-editor-scripts', plugins_url('scripts/elementor-editor.js', __FILE__), array('jquery', 'wp-i18n', 'jquery-ui-dialog'), AIOMATIC_MAJOR_VERSION, true);

            $ajax_params = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'assistant_nonce' => wp_create_nonce('aiomatic-assistant')
            );
            wp_localize_script('aiomatic-editor-scripts', 'aiomatic_ajax_object', $ajax_params);
        }
    }

    function Aiomatic_Elementor_Integration() {
        return Aiomatic_Elementor_Assistant::instance();
    }
    Aiomatic_Elementor_Integration();
}