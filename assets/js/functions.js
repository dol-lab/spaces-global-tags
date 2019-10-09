/**
 * Simple XHR function.
 *
 * @param url
 * @param cb
 */
function remoteSearch(url, cb) {
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
}

let tributeComments = new Tribute(
	{
		trigger: '#',
		values: function(url, cb) {
			remoteSearch( SpacesGlobalTags.routes.commentTags, tags => cb( tags ) );
		},
		lookup: 'name',
		fillAttr: 'name',
		selectTemplate: function(item) {
			return '#' + item.original.name;
		},
		menuItemTemplate: function(item) {
			return '#' + item.original.name;
		},
		spaceSelectsMatch: true
	}
);

if (  document.body.contains( document.getElementById( "comment" ) ) ) {
	tributeComments.attach( document.getElementById( "comment" ) );
}

let tributePosts = new Tribute(
	{
		trigger: '#',
		values: function(url, cb) {
			remoteSearch( SpacesGlobalTags.routes.postTags, tags => cb( tags ) );
		},
		lookup: 'name',
		fillAttr: 'name',
		selectTemplate: function(item) {
			return '#' + item.original.name;
		},
		menuItemTemplate: function(item) {
			return '#' + item.original.name;
		},
		spaceSelectsMatch: true
	}
);

document.addEventListener(
	'onEditorReady',
	function( e ) {
		tributePosts.attach( document.querySelector( "[data-type=post] .ck-editor__editable_inline" ) );
	},
	false
);
