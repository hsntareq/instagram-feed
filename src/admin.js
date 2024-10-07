document.addEventListener('DOMContentLoaded', function () {
	const connectButton = document.getElementById('connect-instagram');
	const statusMessage = document.getElementById('status-message');

	connectButton.addEventListener('click', function () {
		// Calculate the center position for the popup window
		const screenWidth = window.screen.width;
		const screenHeight = window.screen.height;
		const popupWidth = 800;  // Desired width of the popup window
		const popupHeight = 600; // Desired height of the popup window

		const popupLeft = (screenWidth - popupWidth) / 2;
		const popupTop = (screenHeight - popupHeight) / 2;
// https://www.instagram.com/oauth/authorize/third_party?client_id=1992699081248165&redirect_uri=https%3A%2F%2Fgrey-penny.localsite.io%2Fwp-admin%2Fadmin.php%3Fpage%3Dgg-instagram-redirect&scope=user_profile%2Cuser_media&response_type=code&logger_id=9d752666-5fe7-41c7-bcde-5099df9bb4c3
		// Open Instagram OAuth in a new window
		const authWindow = window.open(
			'https://api.instagram.com/oauth/authorize' +
			'?client_id=1992699081248165' +
			'&redirect_uri=' + encodeURIComponent(ggInstagram.admin_url + '?page=gg-instagram-redirect') +
			'&scope=user_profile,user_media' +
			'&response_type=code',
			'Instagram Auth',
			`width=${popupWidth},height=${popupHeight},top=${popupTop},left=${popupLeft}`
		);

		// Listen for messages from the popup
		window.addEventListener('message', function (event) {
			// Ensure the message is coming from the same origin for security
			if (event.origin !== window.location.origin) return;

			if (event.data.success) {
				// Call AJAX to save the access token
				saveAccessToken(event.data.accessToken);
			} else {
				// Display any error messages
				statusMessage.textContent = event.data.error;
			}
		});
	});

	function saveAccessToken(accessToken) {
		const data = new FormData();
		data.append('action', 'gg_save_instagram_data');
		data.append('access_token', accessToken);

		fetch(ggInstagram.ajax_url, {
			method: 'POST',
			body: data,
		})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					statusMessage.textContent = data.data.message; // Update status message
				} else {
					statusMessage.textContent = data.data.message; // Update status message
				}
			})
			.catch(error => {
				console.error('Error:', error);
				statusMessage.textContent = 'An error occurred while saving the access token.';
			});
	}
});
