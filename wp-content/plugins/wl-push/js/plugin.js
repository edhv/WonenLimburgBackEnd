/**
 * Simple plugin to send Push notification through onesignal. The system has a simple security mechanism in the
 * form of a confirmation message to make sure the user is not sending a push by accident.
 * 
 */
jQuery(document).ready(function($){

		var pushMessageButton = document.querySelector('.js-send-push');
		var pushSpinner = document.querySelector('.js-push-spinner');
		/**
		 * Get the current time
		 */
		function timeNow() {
		  var d = new Date(),
		      h = (d.getHours()<10?'0':'') + d.getHours(),
		      m = (d.getMinutes()<10?'0':'') + d.getMinutes();
		      return h+':'+m
		}

		/**
		 * Show an error
		 * 
		 */
		var showError = function($message) {
			document.querySelector('.js-push-feedback').innerHTML = $message;
		}

		/**
		 * Send the push notification through a function defined in the plugin
		 * 
		 */
		var sendPush = function(title, message, options) {
			//console.log(wl_push_settings);
			//
			
			if (options.target === 'all') {
				var confirmation = confirm("Weet u zeker dat u het volgende bericht naar alle gebruikers van de app wilt sturen? \n\n\""+title+"\n"+message+"\"");
				if (!confirmation) return false;
			}


			var data = {
				'action': 'send_push',
				'title':title,
				'message':message,
				'target':options.target
			};

			//console.log()
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				var response = JSON.parse(response);

				if (!response.status) {
					showError("Er is een fout opgetreden bij het versturen van het push bericht.");
				} else {
					showError("Bericht verzonden om " + timeNow());
				}

				pushSpinner.classList.remove('is-active');


			});

			pushSpinner.classList.add('is-active');

		}

		/**
		 * Prepare the push message
		 * @return {[type]} [description]
		 */
		var composePush = function() {
			var targetSelect = document.querySelector('.js-push-target');
			var target = targetSelect.options[targetSelect.selectedIndex].value;

			var title = document.querySelector('.js-push-title').value;
			var message = document.querySelector('.js-push-message').value;

			if (!title || !message) {
				window.alert('Om een push bericht te kunnen verzenden dient u minimaal een Titel en een Bericht op te geven.')
				return false;
			}

			sendPush(title, message, {'target':target})
		}

		/**
		 * Initialize
		 * 
		 */
		var init = function() {
			pushMessageButton.addEventListener('click', composePush);
		}

		if (pushMessageButton) {
			init();
		}
}); 
