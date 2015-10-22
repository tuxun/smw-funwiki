/*
 * This file is part of the MediaWiki extension UploadWizard.
 *
 * UploadWizard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * UploadWizard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UploadWizard.  If not, see <http://www.gnu.org/licenses/>.
 */
( function ( uw ) {
	QUnit.module( 'uw.model.Description', QUnit.newMwEnvironment( {} ) );

	QUnit.test( 'getValue', 3, function ( assert ) {
		var desc = new uw.model.Description();

		assert.strictEqual( desc.getValue(), '', 'Empty value returns empty string.' );

		desc.setText( 'Blah' );
		assert.strictEqual( desc.getValue(), '{{undefined|1=Blah}}', 'Setting value returns template call to language template.' );

		desc.setLanguage( 'en' );
		assert.strictEqual( desc.getValue(), '{{en|1=Blah}}', 'Setting language returns template call to that language template.' );
	} );
}( mediaWiki.uploadWizard ) );
