/**
 * Spaces Global Tags: Auto-complete tag lookup.
 *
 * @package Spaces_Global_Tags
 */

/**
 * Simple XHR function.
 *
 * @param url
 * @param cb
 */
const remoteSearch = function(url, cb) {
	let URL                = url;
	xhr                    = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				var data = JSON.parse( xhr.responseText );
				cb( data );
			} else if (xhr.status === 403) {
				cb( [] );
			}
		}
	};
	xhr.open( "GET", URL,true );
	xhr.send();
};

/**
 * Tribute shared config for comment and post Tags.
 *
 * @type {{lookup: string, spaceSelectsMatch: boolean, fillAttr: string, menuItemTemplate: (function(*): string), trigger: string, selectTemplate: (function(*): string)}}
 */
let tributeConfig = {
	trigger: '#',
	lookup: 'name',
	fillAttr: 'name',
	selectTemplate: function(item) {
		return '#' + item.original.name;
	},
	menuItemTemplate: function(item) {
		return '#' + item.original.name;
	},
	spaceSelectsMatch: true
};

/**
 * Tribute for comment Tags.
 */
let tributeComments = new Tribute(
	Object.assign(
		tributeConfig,
		{ values: function(url, cb) { remoteSearch( SpacesGlobalTags.routes.commentTags, tags => cb( tags ) ); } }
	)
);

/**
 * Attach Tribute for comments to the comment field in WordPress.
 */
if (  document.body.contains( document.getElementById( "comment" ) ) ) {
	tributeComments.attach( document.getElementById( "comment" ) );
}

/**
 * Tribute for post Tags.
 */
let tributePosts = new Tribute(
	Object.assign(
		tributeConfig,
		{ values: function(url, cb) { remoteSearch( SpacesGlobalTags.routes.postTags, tags => cb( tags ) ); } }
	)
);

/**
 * Attach Tribute for posts to the CKEditor for posts and not pages.
 */
document.addEventListener(
	'onEditorReady',
	function() {
		tributePosts.attach( document.querySelector( "[data-type=post] .ck-editor__editable_inline" ) );
	},
	false
);
