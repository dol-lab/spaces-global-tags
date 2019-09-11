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

	/**
	 * Prepares the content to be parsed.
	 *
	 * @param string $content comment or post content.
	 *
	 * @return array $output with a xpath query and DOMDocument.
	 */
	static function setup_content( $content ) {
		$content = wp_pre_kses_less_than( $content );
		$content = wp_kses_normalize_entities( $content );

		$dom = new \DOMDocument;

		libxml_use_internal_errors( true );
		@$dom->loadHTML( '<?xml encoding="UTF-8">' . $content );
		libxml_use_internal_errors( false );

		$xpath = new \DOMXPath( $dom );

		$output = [
			'xpath' => $xpath->query( '//text()' ),
			'dom' => $dom,
		];

		return $output;
	}

	/**
	 * Find tags in a string.
	 *
	 * @param $content
	 *
	 * @return mixed|void
	 */
	static function find_tags( $content ) {
		/**
		 * Placeholder for all tags found.
		 *
		 * var array $tags
		 */
		$tags = [];

		$document = self::setup_content( $content );

		$textNodes = $document['xpath'];

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

	/**
	 * Parses and links tags within a string.
	 * Run on the_content and comment_text.
	 *
	 * @param string $content The content.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return string The linked content.
	 */
	static function tag_links( $content, $taxonomy ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$tags = self::find_tags( $content );

		$tags = array_unique( $tags );

		usort( $tags, [ '\Spaces_Global_Tags\Hashtag_Parser', '_sortByLength' ] );

		/**
		 * TODO: Maybe make them static again, but then we would have to clear out
		 * whenever taxonomy changes.
		 */
		$tag_links = [];

		$tag_info = [];

		foreach ( $tags as $tag ) {
			if ( isset( $tag_info[ $tag ] ) ) {
				continue;
			}
			$info = get_multisite_term_by( 'slug', $tag, $taxonomy );
			if ( ! $info ) {
				$info = get_multisite_term_by( 'name', $tag, $taxonomy );
			}
			$tag_info[ $tag ] = $info;
		}

		$document = self::setup_content( $content );

		$textNodes = $document['xpath'];
		$dom = $document['dom'];

		foreach( $textNodes as $textNode ) {
			if ( ! $textNode->parentNode ) {
				continue;
			}
			$parent = $textNode;
			while( $parent ) {
				if ( ! empty( $parent->tagName ) && in_array( strtolower( $parent->tagName ), array( 'pre', 'code', 'a', 'script', 'style', 'head' ) ) ) {
					continue 2;
				}
				$parent = $parent->parentNode;
			}
			$text = $textNode->nodeValue;
			$totalCount = 0;
			foreach ( $tags as $tag ) {
				if ( empty( $tag_info[ $tag ] ) ) {
					continue;
				}
				if ( empty( $tag_links[ $tag ] ) ) {
					$tag_url = get_multisite_term_link( $tag_info[ $tag ], $taxonomy );
					$replacement = "<a href='" . esc_url( $tag_url ) . "' class='tag'><span class='tag-prefix'>#</span>" . htmlentities( $tag ) . "</a>";
					$replacement = apply_filters( 'spaces_global_tags_tag_link', $replacement, $tag );
					$tag_links[ $tag ] = $replacement;
				} else {
					$replacement = $tag_links[ $tag ];
				}
				$count = 0;
				$text = preg_replace( "/(^|\s|>|\()#$tag(($|\b|\s|<|\)))/", '$1' . $replacement . '$2', $text, -1, $count );
				$totalCount += $count;
			}
			if ( ! $totalCount ) {
				continue;
			}
			$text = wp_pre_kses_less_than( $text );
			$text = wp_kses_normalize_entities( $text );

			$newNodes = new \DOMDocument;

			libxml_use_internal_errors( true );
			@$newNodes->loadHTML( '<?xml encoding="UTF-8"><div>' . $text . '</div>' );
			libxml_use_internal_errors( false );

			foreach( $newNodes->getElementsByTagName( 'body' )->item( 0 )->childNodes->item( 0 )->childNodes as $newNode ) {
				$cloneNode = $dom->importNode( $newNode, true );
				if ( ! $cloneNode ) {
					continue 2;
				}
				$textNode->parentNode->insertBefore( $cloneNode, $textNode );
			}
			$textNode->parentNode->removeChild( $textNode );
		}
		$html = '';
		// Sometime, DOMDocument will put things in the head instead of the body.
		// We still need to keep them in our output.
		$search_tags = array( 'head', 'body' );
		foreach ( $search_tags as $tag ) {
			$list = $dom->getElementsByTagName( $tag );
			if ( 0 === $list->length ) {
				continue;
			}
			foreach ( $list->item( 0 )->childNodes as $node ) {
				$html .= $dom->saveHTML( $node );
			}
		}
		unset( $tag_links );
		unset( $tag_info );

		return $html;
	}

	static function _sortByLength( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	}

}
