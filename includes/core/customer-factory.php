<?php

namespace PC_CPQ\Core;

use \WP_MVC\Core\Abstracts\Factory;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Customer_Factory extends Factory
{

	/**
	 * Get an item from the collection by key.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get( $Customer = false, $default = null )
	{
		$Customer = $this->get_object( $Customer );

		// bail if no item exists
		if ( ! $Customer ) {
			return false;
		}
		
		return $Customer;

		// check if we've loaded the item into the collection already
		// if yes, return it
//		if ( $this->contains( 'id', $Customer->get_id() ) && $Customer->get_id() != 0 ) {
//			return $this->where( 'id', $Customer->get_id() );
//		}
//
//		$this->add( $Customer );
//
//		return $this->last();
	}

	private function get_object( $Customer )
	{
		$customer_id = $this->get_customer_id( $Customer );
		$Customer = new \PC_CPQ\Models\Customer( $customer_id );

		return $Customer;
	}

	/**
	 * Get the inventory ID depending on what was passed.
	 *
	 * @return int|bool false on failure
	 */
	private function get_customer_id( $Customer )
	{
		global $post;

		if ( false === $Customer && isset( $post, $post->ID ) && 'customer' === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $Customer ) ) {
			return $Customer;
		} elseif ( $Customer instanceof \PC_CPQ\Models\Customer ) {
			return $Customer->get_id();
		} elseif ( ! empty( $Customer->ID ) ) {
			return $Customer->ID;
		} else {
			return false;
		}
	}

}
