<?php

namespace Dicentis\Podcast_Post_Type;

class Dipo_Podcast_Shows_Model {

	public function get_shows() {

		$args = array(
			'orderby'       => 'name', 
			'order'         => 'ASC',
			'hide_empty'    => true,
			'cache_domain'  => 'core',
		);

		$podcast_show_slug = dipo_get_podcast_show_slug();
		$podcast_show = get_terms( $podcast_show_slug, $args );

		return isset($podcast_show) ? $podcast_show : null;
	}

	public static function echo_select_shows() {
		$model = new Dipo_Podcast_Shows_Model();

		$shows = $model->get_shows();

		$select_shows = "<select id='dipo-podcast-shows' name='dipo-podcast-shows'>";
		foreach ( $shows as $show ) {
				$select_shows .= "<option value='" . $show->slug . "'>" . $show->name . '</option>';
		}
		$select_shows .= '</select>';

		echo $select_shows;
	}
}