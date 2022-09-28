<?php
/*
Plugin Name: WPForms: recaptcha v3 performance
Plugin URI: https://github.com/kcop4uk/wpforms-recaptcha3-page-speed/
Description: Improves WPForms page speed test performance (https://pagespeed.web.dev) when Google Recaptcha v3 is used. Tested with WPForms Pro 1.7.2.2
Author: kcop4uk
Version: 1.0.0
Author URI: https://fotexlabs.com/
*/

function wpforms_recaptcha3_modify_loading( $tag, $handle, $src ) {
    if ( strpos( $tag, 'recaptcha/api.js?render=' ) !== false ) {

        $content = preg_replace('/(<script[^>]+>)/im', '', $tag);
        $content = preg_replace('/(<\/script>)/im', '', $content);

        $tag = 
            "<script type='text/javascript' id='wpforms-recaptcha-js-after'>
                var callOnce = 0;
                if(typeof grecaptcha === 'undefined') {
                    grecaptcha = {};
                }
                grecaptcha.ready = function(cb){
                    if(typeof grecaptcha === 'undefined') {
                      const c = '___grecaptcha_cfg';
                      window[c] = window[c] || {};
                      (window[c]['fns'] = window[c]['fns']||[]).push(cb);
                    } else {
                      cb();
                    }
                }

                ". $content ."

                function onloadRecaptchaCallback() {
                    grecaptcha.ready( function () {
                        wpformsDispatchEvent( document, 'wpformsRecaptchaLoaded', true );
                    } );

                }

                function LoadReCaptcha3() {
                    if (callOnce) {
                        return;
                    }
                    callOnce = 1;

                    var script = document.createElement('script')
                    script.type = 'text/javascript';
                    script.src = '" . $src . "&onload=onloadRecaptchaCallback';
                    script.id = 'wpforms-recaptcha-js';
                    script.async = false;
                    document.head.appendChild(script);

                }
                function loadReCaptcha3OnScroll() {
                    window.removeEventListener('scroll', loadReCaptcha3OnScroll); 
                    LoadReCaptcha3(); 
                } window.addEventListener('scroll', loadReCaptcha3OnScroll);</script>
            </script>
            ";
    }
    return $tag;
}


add_filter( 'script_loader_tag', 'wpforms_recaptcha3_modify_loading', 99, 3 );