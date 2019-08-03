<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$username = isset( $_POST ) && isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
if ( ! $username ) {
	exit;
}

$email = isset( $_POST ) && isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
if ( ! $email ) {
	exit;
}

$password = isset( $_POST ) && isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
if ( ! $password ) {
	exit;
}

$firstname = isset( $_POST ) && isset( $_POST['firstname'] ) ? sanitize_text_field( $_POST['firstname'] ) : '';

$lastname = isset( $_POST ) && isset( $_POST['lastname'] ) ? sanitize_text_field( $_POST['lastname'] ) : '';

$response = array();
if ( email_exists( $email ) ) {
	$response['error'] = '1';
} else if ( username_exists( $username ) ) {
	$response['error'] = '2';
} else {
	$user_id           = wp_create_user( $username, $password, $email );
	$response['error'] = '0';
	$creds             = array(
		'user_login'    => $username,
		'user_password' => $password,
	);
	wp_signon( $creds, false );

	if ( $firstname ) {
		update_user_meta( $user_id, 'first_name', $firstname );
	}
	if ( $lastname ) {
		update_user_meta( $user_id, 'last_name', $lastname );
	}
}

echo json_encode( $response );

?>