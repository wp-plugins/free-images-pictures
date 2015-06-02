<?php
class FreeImagesPicturesSettingsPage
{

    const TABLE_ROW_NAME = 'free_images_pictures_options';
    public static $DEFAULT_OPTIONS = array (
                                   'api_key' => '1234567812345678',
                                   'max_results' => 20,
                                   'sources' => array('flickr' => 1, 'wikimedia' => 1, 'pixabay' => 1)
                                   );
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $sources;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        
        $this->sources = array(
            'flickr' => 'Flickr',
            'wikimedia' => 'Wikimedia Commons',
            'pixabay' => 'Pixabay'
        );
    }
    
    public function getOptions() {
        return $this->options;
    }
    
    public function getSources() {
        return $this->sources;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Free Images Pictures Settings',
            'Free Images Pictures',
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = wp_parse_args( get_option( self::TABLE_ROW_NAME ), self::$DEFAULT_OPTIONS ); //print_r($this->options);
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Free Images settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            self::TABLE_ROW_NAME, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Custom settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        ); 
        
        add_settings_field(
            'api_key', 
            'API key', 
            array( $this, 'api_key_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );    

        add_settings_field(
            'max_results', 
            'Maximal number of results', 
            array( $this, 'max_results_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );
        
        add_settings_field(
            'enabled_sources',
            'Enabled sources',
            array( $this, 'enabled_sources_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();        
        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = absint( $input['api_key'] );
            
        if( isset( $input['max_results'] ) )
            $new_input['max_results'] = absint( $input['max_results'] );

        /*
        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );
        */

        if( isset( $input['sources'] ) ) {
            $new_input['sources'] = $input['sources'];
        }

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
               '<input type="text" id="id_number" name="%s[id_number]" value="%s" />',
            self::TABLE_ROW_NAME, isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }
    
    public function api_key_callback() {
         printf(
            '<input type="text" id="api_key" name="%s[api_key]" value="%s" />',
            self::TABLE_ROW_NAME, isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function max_results_callback()
    {
        printf(
            '<input type="text" id="max_results" name="%s[max_results]" value="%s" /> max 30, default 20',
            self::TABLE_ROW_NAME, isset( $this->options['max_results'] ) ? esc_attr( $this->options['max_results']) : ''
        );
    }
    
    public function enabled_sources_callback() {
        foreach ($this->sources as $key=>$src) {
            print '<p><input name="'.self::TABLE_ROW_NAME.'[sources]['.$key.']" id="fi-'.$key.'" type="checkbox" value="1" class="code" ' . checked( 1, $this->options['sources'][$key], false ) . ' /><label for="fi-'.$key.'">'.$src.'</label></p>';
        }
    }
    
}

