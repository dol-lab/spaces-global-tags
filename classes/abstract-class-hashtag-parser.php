<?php

namespace Spaces_Global_Tags;

/**
 * Abstract Class Hashtag_Parser.
 *
 * @package Spaces_Global_Tags
 *
 * @since 0.7.0
 */
abstract class Hashtag_Parser {

	/**
	 * Matches on #tag.
	 */
	const TAGS_REGEX = '/(?:^|\s|>|\()#(?!\d{1,2}(?:$|\s|<|\)|\p{P}{1}\s))([\p{L}\p{N}\_\-\.]*[\p{L}\p{N}]+)(?:$|\b|\s|<|\))/iu';

	static function find_tags( $content ) {
		/**
		 * Placeholder for all tags found.
		 *
		 * var array $tags
		 */
		$tags = [];

		/**
		 * Run filters on the text string first.
		 */
		$content = wp_pre_kses_less_than( $content );
		$content = wp_kses_normalize_entities( $content );

		$dom = new DOMDocument;

		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $content );
		libxml_use_internal_errors( false );

		$xpath = new DOMXPath( $dom );
		$textNodes = $xpath->query( '//text()' );

		foreach ( $textNodes as $textNode ) {
			if ( ! $textNode->parentNode ) {
				continue;
			}
			$parent = $textNode;
			while ( $parent ) {
				if ( ! empty( $parent->tagName ) && in_array( strtolower( $parent->tagName ), [ 'pre', 'code', 'a', 'script', 'style', 'head' ] ) ) {
					continue 2;
				}
				$parent = $parent->parentNode;
			}
			$matches = [];
			if ( preg_match_all( Hashtag_Parser::TAGS_REGEX, $textNode->nodeValue, $matches ) ) {
				$tags = array_merge( $tags, $matches[1] );
			}
		}
		// Filters found tags. Passes original found tags and content as args.
		return apply_filters( 'spaces_global_tags_found_tags', $tags, $content );

	}
}
