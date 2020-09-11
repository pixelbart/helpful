<?php
/**
 * CSSPARSER
 * Copyright (C) 2009 Peter Kröner
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @source https://github.com/Schepp/CSS-Parser
 */
namespace Helpful\Core\Vendor;

class Css_Parser
{
	public $css;
	public $parsed;

	/**
	 * LOAD_STRING
	 * Loads a css string
	 */
	public function load_string( $string, $overwrite = false )
	{
		if ( $overwrite) {
			$this->css = $string;
		} else {
			$this->css .= $string;
		}
	}

	/**
	 * LOAD_FILE
	 * Loads a file
	 */
	public function load_file ($file, $overwrite = false )
	{
		$this->load_string( file_get_contents( $file ), $overwrite );
	}

	/**
	 * LOAD_FILES
	 * Loads a number of files
	 */
	public function load_files( $files )
	{
		$files = explode( ';', $files );
		foreach ( $files as $file ) {
			$this->load_file( $file, false );
		}
	}


	/**
	 * PARSE
	 * Parses some CSS into an array
	 */
	public function parse()
	{
		$css = $this->css;
		$css = preg_replace( '/\/\*.*?\*\//ms', '', $css );
		$css = preg_replace( '/([^\'"]+?)(\<!--|--\>)([^\'"]+?)/ms', '$1$3', $css );

		preg_match_all( '/@.+?\}[^\}]*?\}/ms', $css, $blocks );
		array_push( $blocks[0], preg_replace( '/@.+?\}[^\}]*?\}/ms', '', $css ) );

		$ordered = [];

		for ( $i = 0; $i < count( $blocks[0] ); $i++ ) {
			if ( '@media' === substr( $blocks[0][ $i ], 0, 6 ) ) {
				$ordered_key   = preg_replace( '/^(@media[^\{]+)\{.*\}$/ms', '$1', $blocks[0][ $i ] );
				$ordered_value = preg_replace( '/^@media[^\{]+\{(.*)\}$/ms', '$1', $blocks[0][ $i ] );
			} elseif ( '@' === substr( $blocks[0][ $i ], 0, 1 ) )	{
				$ordered_key   = $blocks[0][ $i ];
				$ordered_value = $blocks[0][ $i ];
			} else {
				$ordered_key   = 'main';
				$ordered_value = $blocks[0][ $i ];
			}

			$ordered[ $ordered_key ] = preg_split(
				'/([^\'"\{\}]*?[\'"].*?(?<!\\\)[\'"][^\'"\{\}]*?)[\{\}]|([^\'"\{\}]*?)[\{\}]/',
				trim( $ordered_value, " \r\n\t" ),
				-1,
				PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE
			);
		}
		
		foreach ( $ordered as $key => $val ) {
			$new = [];

			for ( $i = 0; $i < count( $val ); $i++ ) {
				$selector = trim( $val[ $i ], " \r\n\t" );
				
				if ( ! empty( $selector ) ) {
					if ( ! isset( $new[ $selector ] ) ) {
						$new[ $selector ] = [];
					}

					$rules = explode( ';', $val[ ++$i ] );

					foreach ( $rules as $rule ){
						$rule = trim( $rule, " \r\n\t" );

						if ( ! empty( $rule ) ) {
							$rule     = array_reverse( explode( ':', $rule ) );
							$property = trim( array_pop( $rule )," \r\n\t" );
							$value    = implode( ':', array_reverse( $rule ) );
							
							if ( ! isset( $new[ $selector ][ $property ] ) || ! preg_match( '/!important/', $new[ $selector ][ $property ] ) ) {
								$new[ $selector ][ $property ] = $value;
							} elseif ( preg_match( '/!important/', $new[ $selector ][ $property ]) && preg_match( '/!important/', $value ) ) {
								$new[ $selector ][ $property ] = $value;
							}
						}
					}
				}
			}

			$ordered[ $key ] = $new;
		}

		$this->parsed = $ordered;
	}

	/**
	 * GLUE
	 * Turn an array back into CSS
	 */
	public function glue()
	{
		if ( $this->parsed ) {
			$output = '';
			foreach ( $this->parsed as $media => $content ) {
				$prefix = "";
			
				if ( '@media' === substr( $media, 0, 6 ) ) {
					$output .= $media . " {\n";
					$prefix = "\t";
				}
				
				foreach ( $content as $selector => $rules ) {
					$output .= $prefix.$selector . " {\n";
					foreach ( $rules as $property => $value ) {
						$output .= $prefix . "\t" . $property . ': ' . $value;
						$output .= ";\n";
					}
					$output .= $prefix . "}\n\n";
				}
				if ( '@media' === substr( $media, 0, 6 ) ) {
					$output .= "}\n\n";
				}
			}
			return $output;
		}
	}
}