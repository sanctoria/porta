(function ($) {
	'use strict';

	function val(id, fallback) {
		var $el = $('#' + id);
		if (!$el.length) {
			return fallback || '';
		}
		if ($el.is(':checkbox')) {
			return $el.is(':checked');
		}
		return $el.val();
	}

	function hexLum(hex) {
		if (!hex) {
			return null;
		}
		hex = String(hex).replace('#', '');
		if (hex.length === 3) {
			hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		}
		if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
			return null;
		}
		var chan = [];
		for (var i = 0; i < 3; i++) {
			var c = parseInt(hex.substr(i * 2, 2), 16) / 255;
			chan.push(c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4));
		}
		return 0.2126 * chan[0] + 0.7152 * chan[1] + 0.0722 * chan[2];
	}

	function backgroundIsDark(bgType, bg1, bg2, bgUrl) {
		if (bgType === 'image' && bgUrl) {
			return true;
		}
		var l1 = hexLum(bg1);
		if (l1 === null) {
			return false;
		}
		if (bgType === 'gradient' && bg2) {
			var l2 = hexLum(bg2);
			if (l2 !== null) {
				return ((l1 + l2) / 2) < 0.45;
			}
		}
		return l1 < 0.45;
	}

	function updatePreview() {
		var $frame = $('#lf-preview-frame');
		var $form = $frame.find('.lf-preview-form');
		var $btn = $frame.find('.lf-preview-button');
		var $input = $frame.find('.lf-preview-input');
		var $logo = $frame.find('.lf-preview-logo a');
		var bgType = val('bg_type', 'color');
		var bg1 = val('bg_color');
		var bg2 = val('bg_color_2');
		var angle = val('bg_gradient_angle', 160);
		var bgUrl = $('.porta-login-media[data-target="bg_image_id"]').data('url') || (window.portaLoginAdmin && portaLoginAdmin.bgUrl) || '';

		if (bgType === 'image' && bgUrl) {
			$frame.css({ background: (bg1 || '#f0f0f1') + ' url(' + bgUrl + ') center/cover no-repeat' });
		} else if (bgType === 'gradient' && bg1 && bg2) {
			$frame.css({ background: 'linear-gradient(' + angle + 'deg,' + bg1 + ',' + bg2 + ')' });
		} else if (bg1) {
			$frame.css({ background: bg1 });
		} else {
			$frame.css({ background: '#f0f0f1' });
		}

		var formCss = {
			backgroundColor: val('form_bg') || '#fff',
			borderWidth: (val('form_border_width') || 0) + 'px',
			borderStyle: 'solid',
			borderColor: val('form_border_color') || '#c3c4c7',
			borderRadius: (val('form_radius') || 0) + 'px',
			padding: (val('form_padding') || 24) + 'px',
			width: Math.min(parseInt(val('form_width'), 10) || 320, Math.max(220, ($frame.width() || 320) - 48)) + 'px',
			boxShadow: 'none'
		};
		var shadows = {
			light: '0 8px 24px rgba(15,23,32,.08)',
			medium: '0 16px 40px rgba(15,23,32,.16)',
			heavy: '0 24px 60px rgba(15,23,32,.28)'
		};
		if (shadows[val('form_shadow')]) {
			formCss.boxShadow = shadows[val('form_shadow')];
		}
		$form.css(formCss);

		$input.css({
			backgroundColor: val('input_bg') || '#fff',
			color: val('input_text') || '#2c3338',
			borderColor: val('input_border') || '#8c8f94'
		});
		$btn.css({
			background: val('button_bg') || '#2271b1',
			color: val('button_text') || '#fff'
		});
		$frame.find('label, .lf-preview-forget').css('color', val('label_color') || '');
		$frame.find('.lf-preview-nav, .lf-preview-back').css({ color: val('link_color') || '#2271b1', textAlign: 'center' });
		$frame.find('.lf-preview-nav a, .lf-preview-back a').css('color', val('link_color') || '#2271b1');

		var logoLight = $('.porta-login-media[data-target="logo_id"]').data('url') || (window.portaLoginAdmin && portaLoginAdmin.logoUrl) || '';
		var logoDark = $('.porta-login-media[data-target="logo_id_dark"]').data('url') || (window.portaLoginAdmin && portaLoginAdmin.logoUrlDark) || '';
		var logoUrl = (backgroundIsDark(bgType, bg1, bg2, bgUrl) && logoDark) ? logoDark : logoLight;
		var w = parseInt(val('logo_width'), 10) || 84;
		var h = parseInt(val('logo_height'), 10) || 84;
		var maxLogo = 120;
		var scale = Math.min(1, maxLogo / Math.max(w, h));
		var pw = Math.max(32, Math.round(w * scale));
		var ph = Math.max(32, Math.round(h * scale));
		if (logoUrl) {
			$logo.css({ backgroundColor: 'transparent', backgroundImage: 'url(' + logoUrl + ')', width: pw + 'px', height: ph + 'px', backgroundSize: 'contain', backgroundRepeat: 'no-repeat', backgroundPosition: 'center' });
		} else {
			$logo.css({ backgroundColor: 'transparent', backgroundImage: 'url(' + (portaLoginAdmin.defaultLogo || '') + ')', width: '84px', height: '84px' });
		}
		$('#lf-preview-back').toggle(!val('hide_back_to_blog'));
		var loginWith = val('login_with');
		if (loginWith === 'email') {
			$('#lf-preview-user-label').text('Email Address');
		} else if (loginWith === 'username') {
			$('#lf-preview-user-label').text('Username');
		} else {
			$('#lf-preview-user-label').text('Username or Email Address');
		}
	}

	$(function () {
		if ($.fn.wpColorPicker) {
			$('.porta-login-color').wpColorPicker({ change: updatePreview, clear: updatePreview });
		}
		$('.porta-login-form').on('input change', 'input, select, textarea', updatePreview);

		var freeTabs = { design: true, messages: true, login: true };
		function activateTab(tab) {
			if (!tab) {
				return;
			}
			$('.porta-login-tab').removeClass('is-active');
			$('.porta-login-tab[data-tab="' + tab + '"]').addClass('is-active');
			$('.porta-login-panel').removeClass('is-active');
			$('#porta-login-tab-' + tab).addClass('is-active');
			$('.porta-login-form > p.submit').toggle(!!freeTabs[tab]);
			$('.porta-login-active-tab').val(tab);
		}
		$('.porta-login-wrap form').on('submit', function () {
			var tab = $('.porta-login-tab.is-active').data('tab') || '';
			var $field = $(this).find('.porta-login-active-tab');
			if (!$field.length) {
				$field = $('<input type="hidden" class="porta-login-active-tab" name="porta_login_active_tab" />').appendTo(this);
			}
			$field.val(tab);
		});
		$('.porta-login-tab').on('click', function (e) {
			e.preventDefault();
			var tab = $(this).data('tab');
			activateTab(tab);
			if (window.history && history.replaceState) {
				history.replaceState(null, '', '#porta-login-tab-' + tab);
			}
		});
		var hashMatch = (window.location.hash || '').match(/^#porta-login-tab-([A-Za-z0-9_-]+)/);
		if (hashMatch) {
			activateTab(hashMatch[1]);
		}

		$('.porta-login-media-select').on('click', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.porta-login-media');
			var frame = wp.media({
				title: portaLoginAdmin.mediaTitle,
				button: { text: portaLoginAdmin.mediaButton },
				multiple: false
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$wrap.find('input[type="hidden"]').val(att.id);
				$wrap.data('url', att.url);
				$wrap.find('.porta-login-media-preview').html('<img src="' + att.url + '" alt="" />');
				updatePreview();
			});
			frame.open();
		});
		$('.porta-login-media-clear').on('click', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.porta-login-media');
			$wrap.find('input[type="hidden"]').val('');
			$wrap.data('url', '');
			$wrap.find('.porta-login-media-preview').empty();
			updatePreview();
		});
		$('.porta-login-media').each(function () {
			if ($(this).attr('data-url') || $(this).data('url')) {
				return;
			}
			var src = $(this).find('img').attr('src');
			if (src) {
				$(this).data('url', src);
			}
		});

		$('#porta-login-dismiss-upsell').on('click', function () {
			var nonce = $(this).data('nonce');
			$.post(ajaxurl, { action: 'porta_login_dismiss_upsell', nonce: nonce }).always(function () {
				$('#porta-login-upsell').slideUp(150);
			});
		});

		updatePreview();
	});
})(jQuery);
