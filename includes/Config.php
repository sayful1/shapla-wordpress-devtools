<?php

namespace Shapla\Devtools;

defined( 'ABSPATH' ) || exit;

class Config {

	public static function all(): array {
		return [
			'mail' => [
				'host'     => 'smtp.mailtrap.io',
				'port'     => 2525,
				'username' => '792cd84e65fa40',
				'password' => '7ff7b702b825a3',
			],
		];
	}

	public static function get( string $key, $default = null ) {
		$keys    = explode( '.', $key );
		$options = self::all();
		$data    = false;
		foreach ( $keys as $index => $_key ) {
			if ( 0 == $index ) {
				$data = $options[ $_key ] ?? false;
			} else {
				$data = $data[ $_key ] ?? false;
			}
		}

		return $data ?? $default;
	}
}
