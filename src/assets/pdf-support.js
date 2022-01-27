/**
 * PDF Support
 *
 * @author Takuto Yanagida
 * @version 2022-01-27
 */

wp.media.view.AttachmentFilters.Uploaded.prototype.createFilters = function () {
	const type  = this.model.get('type');
	const types = wp.media.view.settings.mimeTypes;
	const uid   = window.userSettings ? parseInt(window.userSettings.uid, 10) : 0;
	let text;

	if (types && type) text = types[type];
	const l10n = wp.media.view.l10n;

	if (this.options.controller._state === 'featured-image') {
		this.filters = {
			all: {  // Images (Default)
				text    : wpinc_medi_pdf_support.label_image,
				props   : { type: 'image', uploadedTo: null, orderby: 'date', order: 'DESC' },
				priority: 10
			},
			image_pdf: {
				text    : wpinc_medi_pdf_support.label_image_pdf,
				props   : { type: 'image_pdf', uploadedTo: null, orderby: 'date', order: 'DESC' },
				priority: 20
			},
			uploaded: {
				text    : l10n.uploadedToThisPost,
				props   : { type: 'image_pdf', uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC' },
				priority: 30
			},
			unattached: {
				text    : l10n.unattached,
				props   : { status: null, uploadedTo: 0, type: null, orderby: 'menuOrder', order: 'ASC' },
				priority: 50
			},
		};
	} else {
		this.filters = {
			all: {
				text    : text || l10n.allMediaItems,
				props   : { uploadedTo: null, orderby: 'date', order: 'DESC', author: null },
				priority: 10
			},
			uploaded: {
				text    : l10n.uploadedToThisPost,
				props   : { uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC', author: null },
				priority: 20
			},
			unattached: {
				text    : l10n.unattached,
				props   : { uploadedTo: 0, orderby: 'menuOrder', order: 'ASC', author: null },
				priority: 50
			}
		};
	}
	if (uid) {
		this.filters.mine = {
			text    : l10n.mine,
			props   : { orderby: 'date', order: 'DESC', author: uid },
			priority: 50
		};
	}
};

wp.media.view.Modal.prototype.on('open', function () {
	jQuery('.media-modal').find('a.media-menu-item').click(function () {
		if (jQuery(this).html() === wpinc_medi_pdf_support.label_featured_image) {
			jQuery('select.attachment-filters option[value="all"]')
				.attr('selected', true)
				.parent()
				.trigger('change');
		}
	});
});

wp.media.featuredImage.frame().on('open', function () {
	// Change the default view to "Uploaded to this post".
	jQuery('select.attachment-filters option[value="all"]')
		.attr('selected', true)
		.parent()
		.trigger('change');
});
