<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kata_options = get_option( 'kata_options' );
if ( isset( $kata_options['updates']['styler']['updated'] ) ) {
	wp_die(
		'Update process already has been done',
		'',
		array(
			'link_url'  => esc_url( admin_url( '/' ) ),
			'link_text' => esc_html__( 'back to dashboard', 'kata-plus' ),
		)
	);
}

?>

<div class="kata-admin kata-dashboard-page wrap about-wrap">
	<?php $this->header(); ?>
	<div class="kata-card kata-primary">
		<div class="kt-updt-wrap">

			<h2>Data Update Panel</h2>
			<p><strong>It is important to get a backup before starting the update process. We won't be responsible for any potential data loss in the future.</strong></p>
			<p>First, get a backup with one of the recommended backup tools. below. Then click on Start Update Process.</p>
			<ul class="backup-plugins">
				<li><img src="https://ps.w.org/updraftplus/assets/icon-128x128.jpg"><a href="<?php echo esc_url( admin_url() ); ?>plugin-install.php?s=UpdraftPlus&tab=search&type=term" target="_blank">UpdraftPlus</a></li>
				<li><img src="https://ps.w.org/duplicator/assets/icon-128x128.png"><a href="<?php echo esc_url( admin_url() ); ?>plugin-install.php?s=Duplicator&tab=search&type=term" target="_blank">Duplicator</a></li>
				<li><img src="https://ps.w.org/backwpup/assets/icon-128x128.png"><a href="<?php echo esc_url( admin_url() ); ?>plugin-install.php?s=BackWPup&tab=search&type=term" target="_blank">BackWPup</a></li>
				<li><img src="https://ps.w.org/all-in-one-wp-migration/assets/icon-128x128.png"><a href="<?php echo esc_url( admin_url() ); ?>plugin-install.php?s=All%2520in%2520one%2520Migration&tab=search&type=term" target="_blank">All in one Migration</a></li>
			</ul>
			<div class="progress-container">
				<div class="progress-bar">0%</div>
			</div>
			<div class="result"></div>
			<div class="return-to-dashboard">
				<div class="backup-confirmation">
					<input type="checkbox" id="have_backup" name="have_backup">
					<label for="have_backup">I Got the Backup</label>
				</div>
				<a class="help-links backtowp-btn" href="<?php echo esc_url( admin_url() ); ?>" target="_blank"><i class="ti-arrow-left"></i> Back to Dashboard</a>
				<button class="update-btn start-progress" onclick="processIds()"><i class="ti-reload"></i> Start Update Process</button>
			</div>
		</div>
	</div>
</div>
<script>
	// Get references to the checkbox and the button
	const haveBackupCheckbox = document.getElementById('have_backup');
	const updateButton = document.querySelector('.start-progress');

	// Function to enable/disable the button based on checkbox state
	function updateButtonState() {
		if (haveBackupCheckbox.checked) {
			updateButton.disabled = false;
			updateButton.style.opacity = '1';
			updateButton.style.cursor = 'pointer';
		} else {
			updateButton.disabled = true;
			updateButton.style.opacity = '0.6';
			updateButton.style.cursor = 'not-allowed';
		}
	}

	// Add an event listener to the checkbox
	haveBackupCheckbox.addEventListener('change', updateButtonState);

	// Initially, call the function to set the button state based on checkbox state
	updateButtonState();

	// Function to send a POST request with an "action" parameter
	function sendPostRequest(url, action, data) {
		const formData = new FormData();
		formData.append('action', action);
		formData.append('data', JSON.stringify(data));
		formData.append('nonce', '<?php echo wp_create_nonce('kata_update_nonce'); ?>');

		const requestOptions = {
			method: 'POST',
			body: formData,
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		};

		return fetch(url, requestOptions)
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP ${response.status}: ${response.statusText}`);
				}
				return response.text().then(text => {
					if (!text.trim()) {
						throw new Error('Empty response from server');
					}
					try {
						return JSON.parse(text);
					} catch (e) {
						console.error('Invalid JSON response:', text);
						throw new Error('Server returned invalid JSON response');
					}
				});
			})
			.catch(error => {
				console.error('Request failed:', error);
				throw error;
			});
	}

	// Function to send a GET request with "action" and "id" as query parameters
	function sendGetRequest(url, action, id) {
		const queryParams = new URLSearchParams();
		queryParams.append('new_to_update_id', id);
		queryParams.append('action', action);
		queryParams.append('nonce', '<?php echo wp_create_nonce('kata_update_nonce'); ?>');

		// Append the query parameters to the URL
		const requestUrl = `${url}?${queryParams.toString()}`;

		return fetch(requestUrl, {
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => {
			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}
			return response.text().then(text => {
				if (!text.trim()) {
					throw new Error('Empty response from server');
				}
				try {
					return JSON.parse(text);
				} catch (e) {
					console.error('Invalid JSON response:', text);
					throw new Error('Server returned invalid JSON response');
				}
			});
		})
		.catch(error => {
			console.error('Request failed:', error);
			throw error;
		});
	}

	// Function to fetch the initial JSON array containing IDs
	function fetchInitialIds() {
		return sendPostRequest('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', 'kata_update_db_initial_ids', {});
	}

	// Function to fetch data for a specific ID
	function fetchDataForId(id) {
		return sendGetRequest('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', 'kata_update_db_fetch_data', id);
	}

	function updateUIWithSuccess(id, title) {
		const resultDiv = document.querySelector('.result');
		const successMessageContainer = document.createElement('div');
		successMessageContainer.classList.add('success-message-container');
		successMessageContainer.style.cssText = 'display: flex; align-items: center; padding: 8px 12px; margin: 5px 0; border-radius: 4px; font-size: 14px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; animation: fadeIn 0.3s ease;';

		const successIcon = document.createElement('span');
		successIcon.classList.add('success-icon');
		successIcon.innerHTML = '&#10004;'; // Checkmark symbol
		successIcon.style.cssText = 'margin-right: 8px; font-weight: bold; color: #28a745;';

		const successMessage = document.createElement('span');
		successMessage.classList.add('success-message');
		successMessage.textContent = `Task "${title}" completed successfully.`;

		successMessageContainer.appendChild(successIcon);
		successMessageContainer.appendChild(successMessage);
		resultDiv.appendChild(successMessageContainer);

		// Scroll to show the latest message
		resultDiv.scrollTop = resultDiv.scrollHeight;

		if ( id === 'regenerate_elementor_data' ) {
			setTimeout(() => {
				window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=kata-plus-theme-activation' ) ); ?>';
			}, 2000);
		}
	}

	function updateUIWithError(id, title, error) {
		const resultDiv = document.querySelector('.result');
		const errorMessageContainer = document.createElement('div');
		errorMessageContainer.classList.add('error-message-container');
		errorMessageContainer.style.cssText = 'display: flex; align-items: center; padding: 8px 12px; margin: 5px 0; border-radius: 4px; font-size: 14px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; animation: fadeIn 0.3s ease;';

		const errorIcon = document.createElement('span');
		errorIcon.classList.add('error-icon');
		errorIcon.innerHTML = '&#10006;'; // X symbol
		errorIcon.style.cssText = 'margin-right: 8px; font-weight: bold; color: #dc3545;';

		const errorMessage = document.createElement('span');
		errorMessage.classList.add('error-message');
		errorMessage.textContent = `Task "${title}" failed: ${error.message}`;

		errorMessageContainer.appendChild(errorIcon);
		errorMessageContainer.appendChild(errorMessage);
		resultDiv.appendChild(errorMessageContainer);

		// Scroll to show the latest message
		resultDiv.scrollTop = resultDiv.scrollHeight;
	}

	// Function to update the progress bar
	function updateProgressBar(current, total, currentTask = '') {
		const progress = (current / total) * 100;
		const progressBar = document.querySelector('.progress-bar');
		progressBar.style.width = `${progress}%`;
		progressBar.style.cssText += 'background: linear-gradient(90deg, #0073aa, #005a87); height: 100%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;';
		
		if (currentTask) {
			progressBar.textContent = `${progress.toFixed(1)}% - ${currentTask}`;
		} else {
			progressBar.textContent = `${progress.toFixed(1)}%`;
		}
	}

	function showInitialMessage() {
		const resultDiv = document.querySelector('.result');
		resultDiv.innerHTML = '<div style="padding: 10px; color: #666; font-style: italic;">Starting update process...</div>';
	}

	// Main function to orchestrate the process
	async function processIds() {
		try {
			const loading = document.querySelector('.update-btn');
			loading.classList.add('loading');
			loading.disabled = true;
			loading.style.cssText += 'position: relative; color: transparent; background: #6c757d; cursor: not-allowed; opacity: 0.8;';
			
			// Add loading spinner
			if (!loading.querySelector('.spinner')) {
				const spinner = document.createElement('div');
				spinner.classList.add('spinner');
				spinner.style.cssText = 'position: absolute; top: 50%; left: 50%; width: 16px; height: 16px; margin: -8px 0 0 -8px; border: 2px solid transparent; border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite;';
				loading.appendChild(spinner);
			}

			showInitialMessage();

			const initialIds = await fetchInitialIds();
			
			// Check if initialIds is valid and has the expected structure
			if (!initialIds || typeof initialIds !== 'object') {
				throw new Error('Invalid response from server: missing or invalid data structure');
			}
			
			const idsObject = initialIds['ids'] || initialIds;
			
			// Check if idsObject is valid
			if (!idsObject || typeof idsObject !== 'object') {
				throw new Error('Invalid response from server: missing ids array');
			}
			
			const totalIds = Object.keys(idsObject).length;
			
			if (totalIds === 0) {
				updateUIWithSuccess('no_tasks', 'No update tasks found');
				updateProgressBar(1, 1);
				return;
			}
			
			// Clear initial message
			const resultDiv = document.querySelector('.result');
			resultDiv.innerHTML = '';
			
			let currentIdIndex = 0;
			let successCount = 0;
			let errorCount = 0;

			for (const id in idsObject) {
				if (idsObject.hasOwnProperty(id)) {
					try {
						const title = idsObject[id];
						updateProgressBar(currentIdIndex, totalIds, title);
						
						console.log(`Processing task: ${id} - ${title}`);
						
						const data = await fetchDataForId(id);
						
						if (data && data.success !== false) {
							updateUIWithSuccess(id, title);
							successCount++;
						} else {
							const errorMsg = data && data.message ? data.message : 'Unknown error occurred';
							updateUIWithError(id, title, new Error(errorMsg));
							errorCount++;
						}
					} catch (error) {
						console.error(`Error processing task ${id}:`, error);
						updateUIWithError(id, idsObject[id] || 'Unknown Task', error);
						errorCount++;
					}

					// Update the progress bar
					currentIdIndex++;
					updateProgressBar(currentIdIndex, totalIds);
					
					// Add a small delay to prevent overwhelming the server
					await new Promise(resolve => setTimeout(resolve, 500));
				}
			}

			// Show final summary
			const summaryDiv = document.createElement('div');
			summaryDiv.style.cssText = 'margin-top: 20px; padding: 15px; border-radius: 5px; background: #f8f9fa; border: 1px solid #dee2e6; font-weight: bold;';
			summaryDiv.innerHTML = `
				<div style="color: #333;">Update Process Completed!</div>
				<div style="color: #28a745; margin-top: 5px;">✓ ${successCount} tasks completed successfully</div>
				${errorCount > 0 ? `<div style="color: #dc3545; margin-top: 5px;">✗ ${errorCount} tasks failed</div>` : ''}
			`;
			document.querySelector('.result').appendChild(summaryDiv);

		} catch (error) {
			console.error('Update process failed:', error);
			updateUIWithError('general', 'Update Process', error);
		} finally {
			const loading = document.querySelector('.update-btn');
			loading.classList.remove('loading');
			loading.disabled = false;
			loading.style.cssText = 'background: #0073aa; color: white; cursor: pointer; opacity: 1;';
			
			// Remove spinner
			const spinner = loading.querySelector('.spinner');
			if (spinner) {
				spinner.remove();
			}
			
			// Re-enable checkbox functionality
			updateButtonState();
		}
	}

	// Add CSS animations
	const style = document.createElement('style');
	style.textContent = `
		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(-10px); }
			to { opacity: 1; transform: translateY(0); }
		}
		.progress-container {
			background: #f1f1f1; border-radius: 10px; height: 20px; 
			margin: 20px 0; overflow: hidden; position: relative;
		}
		.result {
			margin: 20px 0; max-height: 300px; overflow-y: auto; 
			border: 1px solid #e9ecef; border-radius: 5px; padding: 10px; 
			background: #fafafa;
		}
	`;
	document.head.appendChild(style);
</script>
<?php
do_action( 'kata_plus_control_panel' );
