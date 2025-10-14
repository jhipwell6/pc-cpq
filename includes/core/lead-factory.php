<?php

namespace PC_CPQ\Core;

use \WP_MVC\Core\Abstracts\Factory;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Lead_Factory extends Factory
{

	/**
	 * Get an item from the collection by key.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get( $Lead = false, $default = null )
	{
		$Lead = $this->get_object( $Lead );

		// bail if no item exists
		if ( ! $Lead ) {
			return false;
		}

		// check if we've loaded the item into the collection already
		// if yes, return it
		if ( $this->contains( 'id', $Lead->get_id() ) && $Lead->get_id() != 0 ) {
			return $this->where( 'id', $Lead->get_id() );
		}

		$this->add( $Lead );

		return $this->last();
	}

	private function get_object( $Lead )
	{
		$lead_id = $this->get_lead_id( $Lead );
		$Lead = new \PC_CPQ\Models\Lead( $lead_id );

		return $Lead;
	}

	/**
	 * Get the inventory ID depending on what was passed.
	 *
	 * @return int|bool false on failure
	 */
	private function get_lead_id( $Lead )
	{
		global $post;

		if ( false === $Lead && isset( $post, $post->ID ) && 'lead' === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $Lead ) ) {
			return $Lead;
		} elseif ( $Lead instanceof \PC_CPQ\Models\Lead ) {
			return $Lead->get_id();
		} elseif ( ! empty( $Lead->ID ) ) {
			return $Lead->ID;
		} else {
			return false;
		}
	}

}
