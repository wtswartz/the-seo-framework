<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Description
 * @subpackage The_SEO_Framework\Getters\Description
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class The_SEO_Framework\Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Description extends Generate {

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_description_from_custom_field()
	 * @uses $this->get_generated_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real description output.
	 */
	public function get_description( $args = null, $escape = true ) {

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		$desc = $this->get_description_from_custom_field( $args, false )
			 ?: $this->get_generated_description( $args, false );
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 : 1. Now tries to get the homepage social descriptions.
	 *                2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_open_graph_description_from_custom_field()
	 * @uses $this->get_generated_open_graph_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real Open Graph description output.
	 */
	public function get_open_graph_description( $args = null, $escape = true ) {

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		$desc = $this->get_open_graph_description_from_custom_field( $args, false )
			 ?: $this->get_generated_open_graph_description( $args, false );
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field.
	 * Falls back to meta description.
	 *
	 * @since 3.1.0
	 * @see $this->get_open_graph_description()
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string TwOpen Graphitter description.
	 */
	protected function get_open_graph_description_from_custom_field( $args, $escape ) {

		if ( null === $args ) {
			$desc = $this->get_custom_open_graph_description_from_query();
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_open_graph_description_from_args( $args );
		}

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on query.
	 * Falls back to meta description.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the homepage as page prior getting custom field data.
	 * @since 4.0.0 Added term meta item checks.
	 * @see $this->get_open_graph_description()
	 * @see $this->get_open_graph_description_from_custom_field()
	 *
	 * @return string Open Graph description.
	 */
	protected function get_custom_open_graph_description_from_query() {

		$desc = '';
		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $this->is_real_front_page() ) {
			if ( $this->is_static_frontpage() ) {
				$desc = $this->get_option( 'homepage_og_description' )
					 ?: $this->get_post_meta_item( '_open_graph_description' )
					 ?: $this->get_description_from_custom_field();
			} else {
				$desc = $this->get_option( 'homepage_og_description' )
					 ?: $this->get_description_from_custom_field();
			}
		} elseif ( $this->is_singular() ) {
			$desc = $this->get_post_meta_item( '_open_graph_description' )
				 ?: $this->get_description_from_custom_field();
		} elseif ( $this->is_term_meta_capable() ) {
			$desc = $this->get_term_meta_item( 'og_description' )
				 ?: $this->get_description_from_custom_field();
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on arguments.
	 * Falls back to meta description.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 : 1. Now tests for the homepage as page prior getting custom field data.
	 *                2. Now obtains custom field data for terms.
	 * @since 4.0.0 Added term meta item checks.
	 * @see $this->get_open_graph_description()
	 * @see $this->get_open_graph_description_from_custom_field()
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 * @return string Open Graph description.
	 */
	protected function get_custom_open_graph_description_from_args( array $args ) {

		$desc = '';
		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $args['taxonomy'] ) {
			$desc = $this->get_term_meta_item( 'og_description', $args['id'] )
				 ?: $this->get_description_from_custom_field( $args );
		} else {
			if ( $this->is_static_frontpage( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_og_description' )
					 ?: $this->get_post_meta_item( '_open_graph_description', $args['id'] )
					 ?: $this->get_description_from_custom_field( $args );
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_og_description' )
					 ?: $this->get_description_from_custom_field( $args );
			} else {
				$desc = $this->get_post_meta_item( '_open_graph_description', $args['id'] )
					 ?: $this->get_description_from_custom_field( $args );
			}
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Returns the Twitter meta description.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 : 1. Now tries to get the homepage social descriptions.
	 *                2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_twitter_description_from_custom_field()
	 * @uses $this->get_generated_twitter_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real Twitter description output.
	 */
	public function get_twitter_description( $args = null, $escape = true ) {

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		$desc = $this->get_twitter_description_from_custom_field( $args, false )
			 ?: $this->get_generated_twitter_description( $args, false );
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_twitter_description()
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string Twitter description.
	 */
	protected function get_twitter_description_from_custom_field( $args, $escape ) {

		if ( null === $args ) {
			$desc = $this->get_custom_twitter_description_from_query();
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_twitter_description_from_args( $args );
		}

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field, based on query.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 : 1. Now tests for the homepage as page prior getting custom field data.
	 *                2. Now obtains custom field data for terms.
	 * @since 4.0.0 Added term meta item checks.
	 * @see $this->get_twitter_description()
	 * @see $this->get_twitter_description_from_custom_field()
	 *
	 * @return string Twitter description.
	 */
	protected function get_custom_twitter_description_from_query() {

		$desc = '';
		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $this->is_real_front_page() ) {
			if ( $this->is_static_frontpage() ) {
				$desc = $this->get_option( 'homepage_twitter_description' )
					 ?: $this->get_post_meta_item( '_twitter_description' )
					 ?: $this->get_option( 'homepage_og_description' )
					 ?: $this->get_post_meta_item( '_open_graph_description' )
					 ?: $this->get_description_from_custom_field()
					 ?: '';
			} else {
				$desc = $this->get_option( 'homepage_twitter_description' )
					?: $this->get_option( 'homepage_og_description' )
					?: $this->get_description_from_custom_field()
					?: '';
			}
		} elseif ( $this->is_singular() ) {
			$desc = $this->get_post_meta_item( '_twitter_description' )
				 ?: $this->get_post_meta_item( '_open_graph_description' )
				 ?: $this->get_description_from_custom_field()
				 ?: '';
		} elseif ( $this->is_term_meta_capable() ) {
			$desc = $this->get_term_meta_item( 'tw_description' )
				 ?: $this->get_term_meta_item( 'og_description' )
				 ?: $this->get_description_from_custom_field()
				 ?: '';
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field, based on arguments.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 : 1. Now tests for the homepage as page prior getting custom field data.
	 *                2. Now obtains custom field data for terms.
	 * @since 4.0.0 Added term meta item checks.
	 * @see $this->get_twitter_description()
	 * @see $this->get_twitter_description_from_custom_field()
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 * @return string Twitter description.
	 */
	protected function get_custom_twitter_description_from_args( array $args ) {

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $args['taxonomy'] ) {
			$desc = $this->get_term_meta_item( 'tw_description', $args['id'] )
				 ?: $this->get_term_meta_item( 'og_description', $args['id'] )
				 ?: $this->get_description_from_custom_field( $args )
				 ?: '';
		} else {
			if ( $this->is_static_frontpage( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_twitter_description' )
					 ?: $this->get_post_meta_item( '_twitter_description', $args['id'] )
					 ?: $this->get_option( 'homepage_og_description' )
					 ?: $this->get_post_meta_item( '_open_graph_description', $args['id'] )
					 ?: $this->get_description_from_custom_field( $args )
					 ?: '';
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_twitter_description' )
					 ?: $this->get_option( 'homepage_og_description' )
					 ?: $this->get_description_from_custom_field( $args )
					 ?: '';
			} else {
				$desc = $this->get_post_meta_item( '_twitter_description', $args['id'] )
					 ?: $this->get_post_meta_item( '_open_graph_description', $args['id'] )
					 ?: $this->get_description_from_custom_field( $args )
					 ?: '';
			}
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The custom field description.
	 */
	public function get_description_from_custom_field( $args = null, $escape = true ) {

		if ( null === $args ) {
			$desc = $this->get_custom_description_from_query();

			// Generated as backward compat for the filter...
			$args = [
				'id'       => $this->get_the_real_ID(),
				'taxonomy' => $this->get_current_taxonomy(),
			];
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_description_from_args( $args );
		}

		/**
		 * @since 2.9.0
		 * @since 3.0.6 1. Duplicated from $this->generate_description() (deprecated)
		 *              2. Removed all arguments but the 'id' argument.
		 * @param string     $desc The custom-field description.
		 * @param array|null $args The query arguments. Contains 'id' and 'taxonomy'.
		 *                         Is null when query is autodetermined.
		 */
		$desc = (string) \apply_filters( 'the_seo_framework_custom_field_description', $desc, $args );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Gets a custom description, based on expected or current query, without escaping.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the homepage as page prior getting custom field data.
	 * @internal
	 * @see $this->get_description_from_custom_field()
	 *
	 * @return string The custom description.
	 */
	protected function get_custom_description_from_query() {

		$desc = '';

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $this->is_real_front_page() ) {
			if ( $this->is_static_frontpage() ) {
				$desc = $this->get_option( 'homepage_description' )
					 ?: $this->get_post_meta_item( '_genesis_description' )
					 ?: '';
			} else {
				$desc = $this->get_option( 'homepage_description' ) ?: '';
			}
		} elseif ( $this->is_singular() ) {
			$desc = $this->get_post_meta_item( '_genesis_description' ) ?: '';
		} elseif ( $this->is_term_meta_capable() ) {
			$desc = $this->get_term_meta_item( 'description' ) ?: '';
		} elseif ( \is_post_type_archive() ) {
			/**
			 * @since 4.0.6
			 * @param string $desc The post type archive description.
			 */
			$desc = (string) \apply_filters( 'the_seo_framework_pta_description', '' );
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Gets a custom description, based on input arguments query, without escaping.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the homepage as page prior getting custom field data.
	 * @internal
	 * @see $this->get_description_from_custom_field()
	 *
	 * @param array $args Array of 'id' and 'taxonomy' values.
	 * @return string The custom description.
	 */
	protected function get_custom_description_from_args( array $args ) {

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $args['taxonomy'] ) {
			$desc = $this->get_term_meta_item( 'description', $args['id'] ) ?: '';
		} else {
			if ( $this->is_static_frontpage( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_description' )
					 ?: $this->get_post_meta_item( '_genesis_description', $args['id'] )
					 ?: '';
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_description' ) ?: '';
			} else {
				$desc = $this->get_post_meta_item( '_genesis_description', $args['id'] ) ?: '';
			}
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		return $desc;
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 1. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *              2. No longer caches.
	 *              3. Now listens to option.
	 *              4. Added type argument.
	 * @since 3.1.2 1. Now omits additions when the description will be deemed too short.
	 *              2. Now no longer converts additions into excerpt when no excerpt is found.
	 * @since 3.2.2 Now converts HTML characters prior trimming.
	 * @uses $this->generate_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @param string     $type   Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public function get_generated_description( $args = null, $escape = true, $type = 'search' ) {

		if ( ! $this->is_auto_description_enabled( $args ) ) return '';

		if ( null === $args ) {
			$excerpt = $this->get_description_excerpt_from_query();
		} else {
			$this->fix_generation_args( $args );
			$excerpt = $this->get_description_excerpt_from_args( $args );
		}

		if ( ! in_array( $type, [ 'opengraph', 'twitter', 'search' ], true ) )
			$type = 'search';

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @since 4.0.0 1. Deprecated second parameter.
		 *              2. Added third parameter: $args.
		 * @param string     $excerpt The excerpt to use.
		 * @param int        $page_id Deprecated.
		 * @param array|null $args The query arguments. Contains 'id' and 'taxonomy'.
		 *                         Is null when query is autodetermined.
		 */
		$excerpt = (string) \apply_filters( 'the_seo_framework_fetched_description_excerpt', $excerpt, 0, $args );

		$excerpt = $this->trim_excerpt(
			$excerpt,
			0,
			$this->get_input_guidelines()['description'][ $type ]['chars']['goodUpper']
		);

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @param string     $desc The generated description.
		 * @param array|null $args The query arguments. Contains 'id' and 'taxonomy'.
		 *                         Is null when query is autodetermined.
		 */
		$desc = (string) \apply_filters( 'the_seo_framework_generated_description', $excerpt, $args );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Twitter description output.
	 */
	public function get_generated_twitter_description( $args = null, $escape = true ) {
		return $this->get_generated_description( $args, $escape, 'twitter' );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->generate_description()
	 * @staticvar array $cache
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Open Graph description output.
	 */
	public function get_generated_open_graph_description( $args = null, $escape = true ) {
		return $this->get_generated_description( $args, $escape, 'opengraph' );
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_description_excerpt_from_query() {

		static $excerpt;

		if ( isset( $excerpt ) )
			return $excerpt;

		$excerpt = '';

		if ( $this->is_blog_page() ) {
			$excerpt = $this->get_blog_page_description_excerpt();
		} elseif ( $this->is_real_front_page() ) {
			$excerpt = $this->get_front_page_description_excerpt();
		} elseif ( $this->is_archive() ) {
			$excerpt = $this->get_archival_description_excerpt();
		} elseif ( $this->is_singular() ) {
			$excerpt = $this->get_singular_description_excerpt();
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Fixed front-page as blog logic.
	 *
	 * @param array|null $args An array of 'id' and 'taxonomy' values.
	 * @return string
	 */
	protected function get_description_excerpt_from_args( array $args ) {

		$excerpt = '';

		if ( $args['taxonomy'] ) {
			$excerpt = $this->get_archival_description_excerpt( \get_term( $args['id'], $args['taxonomy'] ) );
		} else {
			if ( $this->is_blog_page_by_id( $args['id'] ) ) {
				$excerpt = $this->get_blog_page_description_excerpt();
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$excerpt = $this->get_front_page_description_excerpt();
			} else {
				$excerpt = $this->get_singular_description_excerpt( $args['id'] );
			}
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for the blog page.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_blog_page_description_excerpt() {
		return $this->get_description_additions( [ 'id' => (int) \get_option( 'page_for_posts' ) ] );
	}

	/**
	 * Returns a description excerpt for the front page.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_front_page_description_excerpt() {

		$id = $this->get_the_front_page_ID();

		$excerpt = '';
		if ( $this->is_static_frontpage( $id ) ) {
			$excerpt = $this->get_singular_description_excerpt( $id );
		}
		$excerpt = $excerpt ?: $this->get_description_additions( [ 'id' => $id ] );

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for archives.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now processes HTML tags via s_excerpt_raw() for the author descriptions.
	 *
	 * @param null|\WP_Term $term The term.
	 * @return string
	 */
	protected function get_archival_description_excerpt( $term = null ) {

		if ( $term && \is_wp_error( $term ) )
			return '';

		if ( is_null( $term ) ) {
			$in_the_loop = true;
			$term        = \get_queried_object();
		} else {
			$in_the_loop = false;
		}

		/**
		 * @since 3.1.0
		 * @see `\the_seo_framework()->s_excerpt_raw()` to strip HTML tags neatly.
		 * @param string   $excerpt The short circuit excerpt.
		 * @param \WP_Term $term    The Term object.
		 */
		$excerpt = (string) \apply_filters( 'the_seo_framework_generated_archive_excerpt', '', $term );

		if ( $excerpt ) return $excerpt;

		$excerpt = '';

		if ( $in_the_loop ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
				// WordPress DOES NOT allow HTML in term descriptions, not even if you're a super-administrator.
				// See https://wpvulndb.com/vulnerabilities/9445. We won't parse HTMl tags unless WordPress adds native support.
				$excerpt = ! empty( $term->description ) ? $this->s_description_raw( $term->description ) : '';
			} elseif ( $this->is_author() ) {
				$excerpt = $this->s_excerpt_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} elseif ( \is_post_type_archive() ) {
				/**
				 * @TODO can we even obtain anything useful ourselves?
				 *
				 * @since 4.0.6
				 * @param string $excerpt The archive description excerpt.
				 * @param mixed  $term    The queried object.
				 */
				$excerpt = (string) \apply_filters( 'the_seo_framework_pta_description_excerpt', '', $term );
			} else {
				/**
				 * @since 4.0.6
				 * @param string $excerpt The fallback archive description excerpt.
				 */
				$excerpt = (string) \apply_filters( 'the_seo_framework_fallback_archive_description_excerpt', '' );
			}
		} else {
			$excerpt = ! empty( $term->description ) ? $this->s_description_raw( $term->description ) : '';
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for singular post types.
	 *
	 * @since 3.1.0
	 *
	 * @param int $id The singular ID.
	 * @return string
	 */
	protected function get_singular_description_excerpt( $id = null ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		//* If the post is protected, don't generate a description.
		if ( $this->is_protected( $id ) ) return '';

		return $this->get_excerpt_by_id( '', $id, null, false );
	}

	/**
	 * Returns additions for "Title on Blog name".
	 *
	 * @since 3.1.0
	 * @since 3.2.0 : 1. Now no longer listens to options.
	 *                2. Now only works for the front and blog pages.
	 * @since 3.2.2 Now works for homepages from external requests.
	 * @see $this->get_generated_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $forced Whether to force the additions, bypassing options and filters.
	 * @return string The description additions.
	 */
	protected function get_description_additions( $args, $forced = false ) {

		$this->fix_generation_args( $args );

		if ( $this->is_blog_page_by_id( $args['id'] ) ) {
			$title = $this->get_filtered_raw_generated_title( $args );
			/* translators: %s = Blog page title. Front-end output. */
			$title = sprintf( \__( 'Latest posts: %s', 'autodescription' ), $title );
		} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
			$title = $this->get_home_page_tagline();
		}

		if ( empty( $title ) )
			return '';

		$on       = \_x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
		$blogname = $this->get_blogname();

		/* translators: 1: Title, 2: on, 3: Blogname */
		return trim( sprintf( \__( '%1$s %2$s %3$s', 'autodescription' ), $title, $on, $blogname ) );
	}

	/**
	 * Fetches or parses the excerpt of the post.
	 *
	 * @since 1.0.0
	 * @since 2.8.2 : Added 4th parameter for escaping.
	 * @since 3.1.0 1. No longer returns anything for terms.
	 *              2. Now strips plausible embeds URLs.
	 * @since 4.0.1 The second parameter `$id` now defaults to int 0, instead of an empty string.
	 *
	 * @param string $excerpt    The Excerpt.
	 * @param int    $id         The Post ID.
	 * @param null   $deprecated No longer used.
	 * @param bool   $escape     Whether to escape the excerpt.
	 * @return string The trimmed excerpt.
	 */
	public function get_excerpt_by_id( $excerpt = '', $id = 0, $deprecated = null, $escape = true ) {

		if ( empty( $excerpt ) )
			$excerpt = $this->fetch_excerpt( $id );

		//* No need to parse an empty excerpt.
		if ( ! $excerpt ) return '';

		return $escape ? $this->s_excerpt( $excerpt ) : $this->s_excerpt_raw( $excerpt );
	}

	/**
	 * Fetches excerpt from post excerpt or fetches the full post content.
	 * Determines if a page builder is used to return an empty string.
	 * Does not sanitize output.
	 *
	 * @since 2.5.2
	 * @since 2.6.6 Detects Page builders.
	 * @since 3.1.0 1. No longer returns anything for terms.
	 *              2. Now strips plausible embeds URLs.
	 * @since 4.0.1 Now fetches the real ID when no post is supplied.
	 *              Internally, this was never an issue. @see `$this->get_singular_description_excerpt()`
	 *
	 * @param \WP_Post|int|null $post The Post or Post ID. Leave null to get current post.
	 * @return string The excerpt.
	 */
	public function fetch_excerpt( $post = null ) {

		$post = \get_post( $post ?: $this->get_the_real_ID() );

		/**
		 * @since 2.5.2
		 * Fetch custom excerpt, if not empty, from the post_excerpt field.
		 */
		if ( ! empty( $post->post_excerpt ) ) {
			$excerpt = $post->post_excerpt;
		} elseif ( isset( $post->post_content ) ) {
			// We should actually get the parsed content here... but that can be heavy on the server.
			// We could cache that parsed content, but that'd be asinine for a plugin. WordPress should've done that.
			$excerpt = $this->uses_page_builder( $post->ID ) ? '' : $post->post_content;

			if ( $excerpt ) {
				$excerpt = $this->strip_newline_urls( $excerpt );
				$excerpt = $this->strip_paragraph_urls( $excerpt );
			}
		} else {
			$excerpt = '';
		}

		return $excerpt;
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * Warning: Returns with entities encoded. The output is not safe for printing.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 : 1. Now uses smarter trimming.
	 *                2. Deprecated 2nd parameter.
	 *                3. Now has unicode support for sentence closing.
	 *                4. Now strips last three words when preceded by a sentence closing separator.
	 *                5. Now always leads with (inviting) dots, even if the excerpt is shorter than $max_char_length.
	 * @since 4.0.0 : 1. Now stops parsing earlier on failure.
	 *                2. Now performs faster queries.
	 *                3. Now maintains last sentence with closing punctuations.
	 * @since 4.0.5 : 1. Now decodes the excerpt input, improving accuracy, and so that HTML entities at
	 *                   the end won't be transformed into gibberish.
	 * @since 4.1.0 : 1. Now texturizes the excerpt input, improving accuracy with included closing & final punctuation support.
	 *                2. Now performs even faster queries, in most situations. (0.2ms/0.02ms total (worst/best) @ PHP 7.3/PCRE 11 ).
	 *                   Mind you, this method probably boots PCRE and wptexturize; so, it'll be slower than what we noted--it's
	 *                   overhead that otherwise WP, the theme, or other plugin would cause anyway. So, deduct that.
	 *                3. Now recognizes connector and final punctuations for preliminary sentence bounding.
	 *                4. Leading punctuation now excludes symbols, special annotations, opening brackets and quotes,
	 *                   and marks used in some latin languages like ¡¿.
	 *                5. Is now able to always strip leading punctuation.
	 *                6. It will now strip leading colon characters.
	 *                7. It will now stop counting trailing words towards new sentences when a connector, dash, mark, or ¡¿ is found.
	 * @see https://secure.php.net/manual/en/regexp.reference.unicode.php
	 *
	 * We use `[^\P{Po}\'\"]` because WordPress texturizes ' and " to fall under `\P{Po}`.
	 * This is perfect. Please have the cortesy to credit us when taking it. :)
	 *
	 * @param string $excerpt         The untrimmed excerpt. Expected not to contain any HTML operators.
	 * @param int    $depr            The current excerpt length. No longer needed. Deprecated.
	 * @param int    $max_char_length At what point to shave off the excerpt.
	 * @return string The trimmed excerpt with decoded entities. Needs escaping prior printing.
	 */
	public function trim_excerpt( $excerpt, $depr = 0, $max_char_length = 0 ) {

		// Decode to get a more accurate character length in Unicode.
		$excerpt = html_entity_decode( $excerpt, ENT_QUOTES | ENT_COMPAT, 'UTF-8' );

		// Find all words with $max_char_length, and trim when the last word boundary or punctuation is found.
		preg_match( sprintf( '/.{0,%d}([^\P{Po}\'\":]|[\p{Pc}\p{Pd}\p{Pf}\p{Z}]|$){1}/su', $max_char_length ), trim( $excerpt ), $matches );
		$excerpt = isset( $matches[0] ) ? ( $matches[0] ?: '' ) : '';

		$excerpt = trim( $excerpt );

		if ( ! $excerpt ) return '';

		// Texturize to recognize the sentence structure. Decode thereafter since we get HTML returned.
		$excerpt = \wptexturize( $excerpt );
		$excerpt = html_entity_decode( $excerpt, ENT_QUOTES | ENT_COMPAT, 'UTF-8' );
		/**
		 * Critically optimized, so the $matches don't make much sense. Bear with me:
		 *
		 * @param array $matches : {
		 *    0 : Full excerpt.
		 *    1 : Sentence after leading punctuation (if any), including opening punctuation, marks, and ¡¿, before first punctuation (if any).
		 *    2 : First one character following [1], always some form of punctuation. Won't be set if [3] is set.
		 *    3 : Following [1] until last punctuation that isn't some sort of connecting punctiation that's leading a word-boundary.
		 *    4 : First three words leading [3]. Connecting punctuations that splits words are included as non-countable.
		 *    5 : All extraneous characters leading [5].
		 * }
		 */
		preg_match(
			'/(?:^[\p{P}\p{Z}]*?)([\P{Po}\p{M}\xBF\xA1:\p{Z}]+[\p{Z}\w])(?:([^\P{Po}\p{M}\xBF\xA1:]$(*ACCEPT))|(?>(?(?=.+?\p{Z}*(?:\w+[\p{Pc}\p{Pd}\p{Pf}\p{Z}]*){1,3}|[\p{Po}]$)(.*[\p{Pe}\p{Pf}]$|.*[^\P{Po}\p{M}\xBF\xA1:])|.*$(*ACCEPT)))(?>(.+?\p{Z}*(?:\w+[\p{Pc}\p{Pd}\p{Pf}\p{Z}]*){1,3})|[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:])?)(.+)?/su',
			$excerpt,
			$matches
		);

		if ( isset( $matches[5] ) ) {
			if ( isset( $matches[4] ) ) {
				$excerpt = $matches[1] . $matches[3] . $matches[4] . $matches[5];
			} else {
				$excerpt = $matches[1] . $matches[3] . $matches[5];
			}
		} elseif ( isset( $matches[3] ) ) {
			$excerpt = $matches[1] . $matches[3];
		} elseif ( isset( $matches[2] ) ) {
			$excerpt = $matches[1] . $matches[2];
		} elseif ( isset( $matches[1] ) ) {
			$excerpt = $matches[1];
		}

		/**
		 * @param array $matches: {
		 *    1 : Full match until leading punctuation.
		 *    2 : Leading and spaces punctuation (if any).
		 *    3 : Non-closing leading punctuation and spaces (if any).
		 * }
		 */
		preg_match(
			'/(.+[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}\p{Po}])+?(\p{Z}*?[^\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}]+)?([\p{Pc}\p{Pd}\p{M}\xBF\xA1:;,\p{Z}]+)?/su',
			$excerpt,
			$matches
		);
		if ( isset( $matches[2] ) ) {
			$excerpt = $matches[1] . $matches[2];
		} else {
			// Ignore useless [3], there's no [2], [1] is open-ended; so, add hellip.
			$excerpt = $matches[1] . '...'; // This should be texturized later to &hellip;.
		}

		return trim( $excerpt );
	}

	/**
	 * Determines whether automated descriptions are enabled.
	 *
	 * @since 3.1.0
	 * @access private
	 * @see $this->get_the_real_ID()
	 * @see $this->get_current_taxonomy()
	 *
	 * @param array|null $args An array of 'id' and 'taxonomy' values.
	 *                         Can be null when query is autodetermined.
	 * @return bool
	 */
	public function is_auto_description_enabled( $args ) {

		if ( is_null( $args ) ) {
			$args = [
				'id'       => $this->get_the_real_ID(),
				'taxonomy' => $this->get_current_taxonomy(),
			];
		}

		/**
		 * @since 2.5.0
		 * @since 3.0.0 Now passes $args as the second parameter.
		 * @since 3.1.0 Now listens to option.
		 * @param bool       $autodescription Enable or disable the automated descriptions.
		 * @param array|null $args            The query arguments. Contains 'id' and 'taxonomy'.
		 *                                    Is null when query is autodetermined.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_enable_auto_description',
			[
				$this->get_option( 'auto_description' ),
				$args,
			]
		);
	}
}
