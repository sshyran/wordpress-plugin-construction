<?php
/*
Plugin Name: SMTP URI MU
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Set SMTP options from the SMTP_URI named constant.
Version: 0.3.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-smtp-uri
*/

// @TODO Add DKIM header

/**
 * Set PHPMailer SMTP options from the SMTP_URI named constant.
 *
 * Protocols: smtp://  smtps://  smtpstarttls://  smtptls://
 *
 *     define( 'SMTP_URI', 'smtps://[<USERNAME>:<PASSWORD>@]<HOST>:<PORT>' );
 *
 * Use URL-encoded strings!
 *
 * Mandrill example (%40 stands for the @ sign)
 *
 *     define( 'SMTP_URI', 'smtptls://REGISTERED%40EMAIL:API-KEY@smtp.mandrillapp.com:587' );
 *
 * To set From name and From address use WP Mail From II plugin.
 *
 * @see: https://wordpress.org/plugins/wp-mailfrom-ii/
 * @param: object $mail PHPMailer instance.
 * @return void
 */
function o1_smtp_options( $mail ) {
    if ( ! ( defined( 'SMTP_URI' ) && SMTP_URI ) ) {
        return;
    }

    $uri = parse_url( SMTP_URI );

    // Protocol and encryption
    switch ( $uri['scheme'] ) {
        case 'smtp':
            $mail->SMTPSecure = '';
            $mail->Port = 25;
            break;
        case 'smtps':
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            break;
        case 'smtptls':
        case 'smtpstarttls':
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            break;
        default:
            return;
    }

    // Host name
    if ( empty( $uri['host'] ) ) {
        return;
    }
    $mail->Host = urldecode( $uri['host'] );

    // Port
    if ( is_int( $uri['port'] ) ) {
        $mail->Port = $uri['port'];
    }

    // Authentication
    if ( ! empty( $uri['user'] ) && ! empty( $uri['pass'] ) ) {
        $mail->SMTPAuth = true;
        $mail->Username = urldecode( $uri['user'] );
        $mail->Password = urldecode( $uri['pass'] );
    }

    $mail->isSMTP();

    /**
     * Turn on SMTP debugging.
     */
    //$mail->SMTPDebug = 4;
    //$mail->Debugoutput = 'error_log';

    /**
     * Bcc someone.
     */
    //$mail->addBCC( '<BCC-ADDRESS', '<BCC-NAME>' );
}

add_action( 'phpmailer_init', 'o1_smtp_options' );
