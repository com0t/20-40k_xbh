<?PHP
class WPKlaviyoAdmin {

    const SMS_DISCLOSURE_TEXT = 'By checking this box and entering your phone number above, you consent to receive marketing text messages (such as [promotion codes] and [cart reminders]) from [company name] at the number provided, including messages sent by autodialer. Consent is not a condition of any purchase. Message and data rates may apply. Message frequency varies. You can unsubscribe at any time by replying STOP or clicking the unsubscribe link (where available) in one of our messages. View our Privacy Policy [link] and Terms of Service [link].';

    function __construct() {
        if (is_admin()) {
            add_action( 'init', array( $this, 'includes') );
            $klaviyo_settings = get_option('klaviyo_settings');

            add_action('admin_menu', array(&$this, 'add_options_subpanel'));
            add_filter('plugin_action_links_' . KLAVIYO_BASENAME, array(&$this, 'plugin_settings_link'));
        }
    }

    public function includes() {
        include_once KLAVIYO_PATH . 'includes/admin/class-kl-plugins-screen-updates.php';
    }

    function add_options_subpanel() {
        if (function_exists('add_menu_page') && current_user_can('manage_options')) {
            global $submenu, $klaviyowp;

            add_menu_page('Klaviyo', 'Klaviyo', 'manage_options', 'klaviyo_settings', array($this, 'settings'), KLAVIYO_URL . 'img/klaviyo-logo.png');
            add_submenu_page('klaviyo_settings', 'Help', 'Help', 'manage_options', 'klaviyo_help', array($this, 'help'));

            $submenu['klaviyo_settings'][0][0] = 'Settings';
        }
    }

    function help() {
        $content = '';
        $content = '<ol>
                      <li><a href="#help-1">Where do I find my Klaviyo API keys?</a></li>
                      <li><a href="#help-4">How do I add a Klaviyo email sign up into my sidebar?</a></li>
                    </ol>
                    <p><a name="help-1"></a><h2>1) Where do I find my Klaviyo API keys?</h2></p>
                    <p>
                      You can find your Klaviyo API keys by going to the
                      <a href="https://www.klaviyo.com/account#api-keys-tab">account page</a> in Klaviyo.
                      Your <strong>public</strong> API key will be 6-7 characters long.
                      Your <strong>private</strong> API key will be 7 characters, a hyphen and then 16 more.<br /><br />

                      Once you have connected your Klaviyo account, tracking will be enabled for visitors.
                    </p>
                    <p><a name="help-2"></a><h2>2) How do I add a Klaviyo email sign up into my sidebar?</h2></p>
                    <p>
                      Make sure you have connected your Klaviyo account on the Klaviyo settings page.<br />
                      Then you can find the widget under Appearance &raquo; Widgets titled &quot;Klaviyo: Email Sign Up&quot;.
                    </p>';

        $content = $this->postbox('klaviyo-help', 'FAQ', $content);
        $this->admin_wrap('Klaviyo Plugin Help', $content);
    }

    function settings() {
        $klaviyo_settings = $this->process_settings();
        $content = '';

        if (function_exists('wp_nonce_field')) {
            $content .= wp_nonce_field('klaviyo-update-settings', '_wpnonce', true, false);
        }

        $sms_list_id = isset( $klaviyo_settings['klaviyo_sms_list_id'] ) ? $klaviyo_settings['klaviyo_sms_list_id'] : null;
        $sms_subscribe_checkbox = isset( $klaviyo_settings['klaviyo_sms_subscribe_checkbox'] ) ? $klaviyo_settings['klaviyo_sms_subscribe_checkbox'] : false;
        $sms_consent_text = isset( $klaviyo_settings['klaviyo_sms_consent_text'] ) ? $klaviyo_settings['klaviyo_sms_consent_text'] : null;
        $sms_disclosure_text = isset( $klaviyo_settings['klaviyo_sms_consent_disclosure_text'] ) ? $klaviyo_settings['klaviyo_sms_consent_disclosure_text'] : null;

        $content .= '<div xmlns="http://www.w3.org/1999/html">
                    <form>
                        <section style="margin:20px 0px 20px">
                            <label for="klaviyo_public_api_key"><b>Public API Key</b></label>
                            <input type="text" class="regular-text" name="klaviyo_public_api_key" style="display:block" value="' . $klaviyo_settings['public_api_key'] . '" />
                            <p style="margin: 2px"><small>You can find them on your <a href="https://www.klaviyo.com/account#api-keys-tab">Klaviyo account page</a></small></p>
                        </section>

                        <section style="margin:20px 0px 0px">
                            <input type="checkbox" name="klaviyo_popup" value="true" ' . checked($klaviyo_settings['klaviyo_popup'], 'true', false) . ' />
                            <label for="klaviyo_popup">Enable Klaviyo signup forms</label>
                        </section>

                        <section style="margin:10px 0px">
                            <input type="checkbox" name="admin_settings_message" value="true" ' . checked($klaviyo_settings['admin_settings_message'], 'true', false) . ' />
                            <label for="admin_settings_message">Disable configuration warning</label>
                        </section>

                        <section style="margin:20px 0px 20px">
                            <p>
                                <b style="font-size: larger">Subscribe contacts at checkout</b></br>
                                <small>Contacts will be subscribed to the specified list when they click "Place Order"</small>
                            </p>

                            <label style="display:block" for="klaviyo_newsletter_list_id"><b>Klaviyo List ID for Email</b></label>
                            <input type="text" class="regular-text" name="klaviyo_newsletter_list_id" placeholder="Email list ID" style="display:block" value="' . $klaviyo_settings['klaviyo_newsletter_list_id'] . '" />

                            <label style="display:block;margin:5px 0px 0px 0px" for="klaviyo_sms_list_id"><b>Klaviyo List ID for SMS</b></label>
                            <input type="text" class="regular-text" name="klaviyo_sms_list_id" placeholder="SMS list ID" style="display:block" value="' . $sms_list_id . '" />

                            <p style="margin: 2px"><small><a href="https://help.klaviyo.com/hc/en-us/articles/115005078647-Find-a-List-ID">How to find List ID</a></small></p>

                        </section>

                        <section style="margin:20px 0px 20px">
                            <p style="margin-bottom: 5px"><b style="font-size: large"> Email </b></p>
                            <input type="checkbox" name="klaviyo_subscribe_checkbox" value="true" ' . checked($klaviyo_settings['klaviyo_subscribe_checkbox'], 'true', false) . ' />
                            <label for="klaviyo_subscribe_checkbox">Subscribe contacts to email marketing</label>

                            <p style="margin:2px"><small>Adds a checkbox to the checkout page for opt-in</small></p>
                            <label style="display:block;margin:10px 0px" for="klaviyo_newsletter_text"><b>Subscribe to newsletter text</b></label>
                            <input type="text" class="regular-text" name="klaviyo_newsletter_text" placeholder="Subscribe to email updates" style="display:block;margin:0px;width=100%" value="' . $klaviyo_settings['klaviyo_newsletter_text'] . '" />

                            <p style="margin-bottom: 5px"><b style="font-size: large"> SMS </b></p>
                            <input type="checkbox" name="klaviyo_sms_subscribe_checkbox" value="true" ' . checked($sms_subscribe_checkbox, 'true', false) . ' />
                            <label for="klaviyo_sms_subscribe_checkbox">Subscribe contacts to SMS marketing</label>

                            <p style="margin:2px"><small>Adds a checkbox to the checkout page for opt-in. You need to first <a href="https://help.klaviyo.com/hc/en-us/articles/360039190611-On-Demand-Training-Getting-Started-with-Klaviyo-SMS">set up SMS in Klaviyo</a></small></p>
                            <label style="display:block;margin:10px 0px" for="klaviyo_sms_consent_text"><b>SMS opt-in checkbox text</b></label>
                            <input type="text" class="regular-text" name="klaviyo_sms_consent_text" placeholder="Subscribe to SMS updates" style="display:block;margin:0px;width=100%" value="' . $sms_consent_text . '" />

                        </section>

                        <section style="margin:20px 0px 20px">
                            <label style="display:block;margin:10px 0px" for="klaviyo_sms_consent_disclosure_text"><b>SMS consent disclosure text</b></label>
                            <textarea rows="10" cols="20" class="regular-text" name="klaviyo_sms_consent_disclosure_text" placeholder="' . self::SMS_DISCLOSURE_TEXT . '" >' . $sms_disclosure_text . '</textarea>

                            <p style="margin:2px"><small>You must include disclosure language for TCPA compliance. You should also update your Terms of Service and Privacy Policy to include the terms of your SMS marketing program</small></p>
                            <p><a href="https://help.klaviyo.com/hc/en-us/articles/360035055312-About-US-SMS-Compliance-Laws">Learn more about SMS consent and compliance</a></p>
                        </section>
                    </form>
                    </div>';

        $wrapped_content = $this->postbox('klaviyo-settings', 'Connect to Klaviyo', $content);

        $this->admin_wrap('Klaviyo Settings', $wrapped_content);
    }

    function process_settings() {
        $klaviyo_notification = new WPKlaviyoNotification('settings_update');

        if (!empty($_POST['klaviyo_option_submitted'])) {

            $klaviyo_settings = get_option('klaviyo_settings');

            if ($_GET['page'] == 'klaviyo_settings' && check_admin_referer('klaviyo-update-settings')) {
                if (isset($_POST['klaviyo_public_api_key']) && strlen($_POST['klaviyo_public_api_key']) < 8) {
                    $klaviyo_settings['public_api_key'] = $_POST['klaviyo_public_api_key'];
                }

                $klaviyo_setting_keys = [
                    'klaviyo_public_api_key',
                    'admin_settings_message',
                    'klaviyo_subscribe_checkbox',
                    'klaviyo_newsletter_list_id',
                    'klaviyo_newsletter_text',
                    'klaviyo_popup',
                    'klaviyo_sms_subscribe_checkbox',
                    'klaviyo_sms_list_id',
                    'klaviyo_sms_consent_text',
                    'klaviyo_sms_consent_disclosure_text',
                ];
                $klaviyo_updated_settings = array_fill_keys( $klaviyo_setting_keys, '' );

                foreach($_POST as $key => $value) {
                    if ( in_array( $key, array( 'klaviyo_newsletter_text', 'klaviyo_sms_consent_text', 'klaviyo_sms_consent_disclosure_text', ) ) ) {
                        $value = trim( stripslashes( $value ) );
                    }

                    $klaviyo_updated_settings[$key] = $value;
                }

                $klaviyo_settings = array_merge($klaviyo_settings, $klaviyo_updated_settings);

                if ( empty( $klaviyo_settings['klaviyo_sms_consent_disclosure_text'] ) ) {
                    $klaviyo_settings['klaviyo_sms_consent_disclosure_text'] = self::SMS_DISCLOSURE_TEXT;
                }

                if ( $klaviyo_settings['klaviyo_subscribe_checkbox'] && !$klaviyo_settings['klaviyo_newsletter_list_id'] ) {
                    $klaviyo_notification->admin_message( 'add_email_list_id', 10 );
                }

                if ( $klaviyo_settings['klaviyo_sms_subscribe_checkbox'] && !$klaviyo_settings['klaviyo_sms_list_id'] ) {
                    $klaviyo_notification->admin_message( 'add_sms_list_id', 10 );
                }

                if (
                    $klaviyo_settings['klaviyo_sms_list_id'] == $klaviyo_settings['klaviyo_newsletter_list_id']
                    && !empty( $klaviyo_settings['klaviyo_sms_list_id'] )
                    && !empty( $klaviyo_settings['klaviyo_newsletter_list_id'] )
                ) {
                    $klaviyo_notification->admin_message( 'same_list_ids', 10 );
                }

                $klaviyo_notification->display_message( 3 );
                update_option( 'klaviyo_settings', $klaviyo_settings );
            }
        }

        return get_option('klaviyo_settings');
    }

    function plugin_settings_link($links) {
        $settings_link = '<a href="' . KLAVIYO_ADMIN . 'admin.php?page=klaviyo_settings">Settings</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    function show_plugin_support() {
        $content = '<p>First, check the <a href="' . KLAVIYO_ADMIN . 'admin.php?page=klaviyo_help">Help Section</a>. If you still have questions or want to give feedback, send an email to <a href="http://docs.klaviyo.com/customer/portal/emails/new">Klaviyo support.</a></p>';
        return $this->postbox('klaviyo-support', 'Help / Feedback', $content);
    }

    function postbox($id, $title, $content) {
        $wrapper = '';
        $wrapper .= '<div id="' . $id . '" class="postbox">';
        $wrapper .=   '<div class="handlediv" title="Click to toggle"><br /></div>';
        $wrapper .=   '<h2 class="hndle" style="font-size: large"><span>' . $title . '</span></h2>';
        $wrapper .=   '<div class="inside">' . $content . '</div>';
        $wrapper .= '</div>';
        return $wrapper;
    }

    function admin_wrap($title, $content) {

        $showpluginsupport = $this->show_plugin_support();

        echo <<<EOT
        <div class="wrap">
          <div class="dashboard-widgets-wrap">
            <h2>{$title}</h2>
            <form method="post" action="">
              <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container" style="width:60%;">
                  <div class="meta-box-sortables ui-sortable">
                     {$content}
                    <p class="submit">
                      <input type="submit" name="klaviyo_option_submitted" class="button-primary" value="Save Settings" />
                    </p>
                  </div>
                </div>
                <div class="postbox-container" style="width:40%;">
                  <div class="meta-box-sortables ui-sortable">
                    {$showpluginsupport}
                  </div>
                </div>
                </div>
            </form>
          </div>
        </div>
EOT;

    }
}
?>
