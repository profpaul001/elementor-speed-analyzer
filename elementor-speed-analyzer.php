<?php
/**
 * Plugin Name: Elementor Speed Analyzer
 * Description: Analizza i tempi di caricamento dei componenti Elementor e identifica i "colpevoli" della lentezza.
 * Version: 1.0.0
 * Author: Il tuo nome
 * Author URI: https://profpaul.icu
 * Text Domain: elementor-speed-analyzer
 * License: GPL-2.0+
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class ElementorSpeedAnalyzer {
    
    public function __construct() {
        // Aggiungi menu admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Inizializza lo script di analisi
        add_action('wp_enqueue_scripts', array($this, 'enqueue_analyzer_script'));
        
        // Endpoint AJAX per salvare i dati
        add_action('wp_ajax_save_elementor_timing', array($this, 'save_timing_data'));
        add_action('wp_ajax_get_elementor_analysis', array($this, 'get_elementor_analysis'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Elementor Speed Analyzer',
            'Speed Analyzer',
            'manage_options',
            'elementor-speed-analyzer',
            array($this, 'render_admin_page'),
            'dashicons-performance',
            99
        );
    }
    
    public function enqueue_analyzer_script() {
        // Carica solo nelle pagine Elementor front-end
        if (isset($_GET['elementor-speed-analyze']) || 
            (function_exists('elementor_load_plugin_textdomain') && 
             !is_admin())) {
            
            wp_enqueue_script(
                'elementor-speed-analyzer-js',
                plugin_dir_url(__FILE__) . 'assets/js/analyzer.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script(
                'elementor-speed-analyzer-js',
                'speed_analyzer_vars',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('elementor_speed_analyzer_nonce')
                )
            );
        }
    }
    
    public function save_timing_data() {
        // Verifica sicurezza
        check_ajax_referer('elementor_speed_analyzer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Accesso negato');
            return;
        }
        
        $timing_data = isset($_POST['timing_data']) ? $_POST['timing_data'] : array();
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if (!empty($timing_data) && $page_id > 0) {
            update_post_meta($page_id, '_elementor_speed_data', $timing_data);
            wp_send_json_success('Dati salvati con successo');
        } else {
            wp_send_json_error('Dati non validi');
        }
    }
    
    public function get_elementor_analysis() {
        // Verifica sicurezza
        check_ajax_referer('get_elementor_analysis_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Accesso negato');
            return;
        }
        
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        if ($page_id > 0) {
            $timing_data = get_post_meta($page_id, '_elementor_speed_data', true);
            
            if (!empty($timing_data)) {
                wp_send_json_success($timing_data);
            }
        }
        
        wp_send_json_error('Dati non trovati');
    }
    
    public function render_admin_page() {
        // Il codice per renderizzare la pagina admin
        include plugin_dir_path(__FILE__) . 'views/admin-page.php';
    }
}

$elementor_speed_analyzer = new ElementorSpeedAnalyzer();