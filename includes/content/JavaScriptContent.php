<?php
/**
 * Content for JavaScript pages.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 1.21
 *
 * @file
 * @ingroup Content
 *
 * @author Daniel Kinzler
 */

/**
 * Content for JavaScript pages.
 *
 * @ingroup Content
 */
class JavaScriptContent extends TextContent {

	/**
	 * @param string $text JavaScript code.
	 * @param string $modelId the content model name
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_JAVASCRIPT ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * Returns a Content object with pre-save transformations applied using
	 * Parser::preSaveTransform().
	 *
	 * @param Title $title
	 * @param User $user
	 * @param ParserOptions $popts
	 *
	 * @return JavaScriptContent
	 */
	public function preSaveTransform( Title $title, User $user, ParserOptions $popts ) {
		global $wgParser;
		// @todo Make pre-save transformation optional for script pages
		// See bug #32858

		$text = $this->getNativeData();
		$pst = $wgParser->preSaveTransform( $text, $title, $user, $popts );

		return new static( $pst );
	}

	/**
	 * @return string JavaScript wrapped in a <pre> tag.
	 */
	protected function getHtml() {
		$html = "";
		$html .= "<pre class=\"mw-code mw-js\" dir=\"ltr\">\n";
		$html .= htmlspecialchars( $this->getNativeData() );
		$html .= "\n</pre>\n";

		return $html;
	}

}
